<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudPago;
use App\Models\SolicitudPagoEstado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SolicitudesPedidosController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudPago::with([
                'cliente',
                'carrito.productos.producto',
                'estados' => function($q) {
                    $q->latest();
                },
                'moneda' // ← AGREGADO
            ])
            ->latest();

        // Filtros
        if ($request->filled('estado')) {
            $query->whereHas('estados', function($q) use ($request) {
                $q->where('estado', $request->estado)
                ->whereIn('id', function($sub) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('solicitud_pagos_estados')
                        ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
                });
            });
        }

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        // Fechas por defecto: últimos 3 meses hasta fin de mes actual
        $fechaDesde = $request->fecha_desde
            ? Carbon::parse($request->fecha_desde)->startOfDay()
            : Carbon::now()->subMonths(3)->startOfMonth();

        $fechaHasta = $request->fecha_hasta
            ? Carbon::parse($request->fecha_hasta)->endOfDay()
            : Carbon::now()->endOfMonth();

        $query->whereBetween('created_at', [$fechaDesde, $fechaHasta]);

        if ($request->filled('buscar')) {
            $search = $request->buscar;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                ->orWhere('monto', 'LIKE', "%{$search}%")
                ->orWhereHas('cliente', function($cq) use ($search) {
                    $cq->where('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('apellidos', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('nro_documento', 'LIKE', "%{$search}%");
                });
            });
        }

        $solicitudes = $query->paginate(15)->appends($request->all());

        // Estadísticas mejoradas
        $estadisticas = [
            'pendientes' => SolicitudPago::whereHas('estados', function($q) {
                $q->where('estado', 'solicitado')
                ->whereIn('id', function($sub) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('solicitud_pagos_estados')
                        ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
                });
            })->count(),

            'aprobadas' => SolicitudPago::whereHas('estados', function($q) {
                $q->where('estado', 'aprobado')
                ->whereIn('id', function($sub) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('solicitud_pagos_estados')
                        ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
                });
            })->count(),

            'rechazadas' => SolicitudPago::whereHas('estados', function($q) {
                $q->where('estado', 'rechazado')
                ->whereIn('id', function($sub) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('solicitud_pagos_estados')
                        ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
                });
            })->count(),

            'total' => SolicitudPago::count(),

            // Total de SOLO las aprobadas, agrupado por moneda
            'totales_por_moneda' => SolicitudPago::whereHas('estados', function($q) {
                    $q->where('estado', 'aprobado')
                    ->whereIn('id', function($sub) {
                        $sub->select(DB::raw('MAX(id)'))
                            ->from('solicitud_pagos_estados')
                            ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
                    });
                })
                ->with('moneda')
                ->get()
                ->groupBy('moneda_id')
                ->map(function($grupo) {
                    $moneda = $grupo->first()->moneda;
                    return [
                        'moneda' => $moneda ? $moneda->codigo_iso : 'DESCONOCIDA',
                        'simbolo' => $moneda ? $moneda->simbolo : 'S/',
                        'total' => $grupo->sum('monto')
                    ];
                })
                ->values()
        ];

        return view('admin.solicitudes-pedidos.index', compact('solicitudes', 'estadisticas', 'fechaDesde', 'fechaHasta'));
    }

    public function show($id)
    {
        $solicitud = SolicitudPago::with([
                'cliente',
                'carrito.productos.producto',
                'estados' => function($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'moneda',
                'cupon'
            ])
            ->findOrFail($id);

        return view('admin.solicitudes-pedidos.show', compact('solicitud'));
    }

    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:aprobado,rechazado',
            'comentarios' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $solicitud = SolicitudPago::with(['carrito.productos.producto'])->findOrFail($id);

            // Verificar que la solicitud no tenga ya un estado final
            $ultimoEstado = $solicitud->estados()->latest()->first();
            if ($ultimoEstado && in_array($ultimoEstado->estado, ['aprobado', 'rechazado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya tiene un estado final y no puede modificarse'
                ], 400);
            }

            // Crear nuevo estado
            $estado = SolicitudPagoEstado::create([
                'solicitud_pago_id' => $solicitud->id,
                'estado' => $request->estado,
                'comentarios' => $request->comentarios ?? ($request->estado == 'aprobado'
                    ? 'Solicitud aprobada por el administrador'
                    : 'Solicitud rechazada por el administrador')
            ]);

            // Si se rechaza, restaurar stock (importante para inventario)
            if ($request->estado == 'rechazado') {
                foreach ($solicitud->carrito->productos as $item) {
                    $producto = $item->producto;

                    // Solo restaurar stock para productos físicos
                    if ($producto->esFisico() && $producto->stock) {
                        $producto->stock->aumentarStock($item->cantidad);

                        Log::info('Stock restaurado por rechazo', [
                            'producto' => $producto->nombre,
                            'cantidad' => $item->cantidad,
                            'solicitud_id' => $solicitud->id
                        ]);
                    }
                }
            }

            // NOTA: El envío de correos lo implementaremos después si es necesario
            // if ($request->estado == 'aprobado' || $request->estado == 'rechazado') {
            //     Aquí iría la lógica de envío de correos (futura implementación)
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'estado' => $estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar estado de solicitud: ' . $e->getMessage(), [
                'solicitud_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verComprobante($id)
    {
        $solicitud = SolicitudPago::findOrFail($id);

        if (!$solicitud->imagen_1) {
            abort(404, 'No hay comprobante disponible');
        }

        return response()->file(Storage::disk('public')->path($solicitud->imagen_1));
    }
}
