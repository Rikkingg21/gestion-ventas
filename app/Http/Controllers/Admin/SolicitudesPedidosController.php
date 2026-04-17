<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudPago;
use App\Models\SolicitudPagoEstado;
use App\Models\MetodoPago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SolicitudesPedidosController extends Controller
{
    private function getViewData($extra = [])
    {
        return array_merge([
            'currentUser' => Auth::guard('admin')->user()
        ], $extra);
    }

    private function applyFilters($query, Request $request)
    {
        // Filtro por estado
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

        // Filtro por método de pago
        if ($request->filled('metodo_pago')) {
            $query->whereHas('metodoPago', function($q) use ($request) {
                $q->where('slug', $request->metodo_pago);
            });
        }

        // Búsqueda general
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

        return $query;
    }

    private function getDateRange(Request $request)
    {
        return [
            'desde' => $request->filled('fecha_desde')
                ? Carbon::parse($request->fecha_desde)->startOfDay()
                : Carbon::now()->subMonths(3)->startOfMonth(),
            'hasta' => $request->filled('fecha_hasta')
                ? Carbon::parse($request->fecha_hasta)->endOfDay()
                : Carbon::now()->endOfMonth()
        ];
    }

    private function getEstadisticas()
    {
        // Contar solicitudes por estado
        $pendientes = SolicitudPago::whereHas('estados', function($q) {
            $q->where('estado', 'solicitado')
              ->whereIn('id', function($sub) {
                  $sub->select(DB::raw('MAX(id)'))
                      ->from('solicitud_pagos_estados')
                      ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
              });
        })->count();

        $aprobadas = SolicitudPago::whereHas('estados', function($q) {
            $q->where('estado', 'aprobado')
              ->whereIn('id', function($sub) {
                  $sub->select(DB::raw('MAX(id)'))
                      ->from('solicitud_pagos_estados')
                      ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
              });
        })->count();

        $rechazadas = SolicitudPago::whereHas('estados', function($q) {
            $q->where('estado', 'rechazado')
              ->whereIn('id', function($sub) {
                  $sub->select(DB::raw('MAX(id)'))
                      ->from('solicitud_pagos_estados')
                      ->whereColumn('solicitud_pago_id', 'solicitud_pagos.id');
              });
        })->count();

        // Total de SOLO las aprobadas, agrupado por moneda
        $totalesPorMoneda = SolicitudPago::whereHas('estados', function($q) {
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
            ->values();

        return [
            'pendientes' => $pendientes,
            'aprobadas' => $aprobadas,
            'rechazadas' => $rechazadas,
            'total' => $pendientes + $aprobadas + $rechazadas,
            'totales_por_moneda' => $totalesPorMoneda
        ];
    }

    private function restoreStock($solicitud)
    {
        if (!$solicitud->carrito) return;

        foreach ($solicitud->carrito->productos as $item) {
            $producto = $item->producto;

            if ($producto && $producto->esFisico() && $producto->stock) {
                $producto->stock->aumentarStock($item->cantidad);

                Log::info('Stock restaurado por rechazo', [
                    'producto' => $producto->nombre,
                    'cantidad' => $item->cantidad,
                    'solicitud_id' => $solicitud->id
                ]);
            }
        }
    }

    private function getImagePath($solicitud, $hash)
    {
        for ($i = 1; $i <= 3; $i++) {
            $imagenCampo = 'imagen_' . $i;
            if (isset($solicitud->$imagenCampo) && str_contains($solicitud->$imagenCampo, $hash)) {
                return [
                    'numero' => $i,
                    'path' => storage_path('app/public/' . $solicitud->$imagenCampo)
                ];
            }
        }
        return null;
    }

    public function index(Request $request)
    {
        $query = SolicitudPago::with([
            'cliente',
            'carrito.productos.producto',
            'estados' => fn($q) => $q->latest(),
            'moneda',
            'metodoPago'
        ])->latest();

        $query = $this->applyFilters($query, $request);

        $fechas = $this->getDateRange($request);
        $query->whereBetween('created_at', [$fechas['desde'], $fechas['hasta']]);

        $solicitudes = $query->paginate(15)->appends($request->all());

        $estadisticas = $this->getEstadisticas();
        $metodosPago = MetodoPago::where('is_active', true)->get();

        return view('admin.solicitudes-pedidos.index', array_merge(
            $this->getViewData(compact('solicitudes', 'estadisticas', 'metodosPago')),
            ['fechaDesde' => $fechas['desde'], 'fechaHasta' => $fechas['hasta']]
        ));
    }

    public function show($id)
    {
        $solicitud = SolicitudPago::with([
            'cliente',
            'carrito.productos.producto',
            'estados' => fn($q) => $q->orderBy('created_at', 'desc'),
            'moneda',
            'cupon',
            'metodoPago'
        ])->findOrFail($id);

        // Decodificar info_pago si es string
        if (is_string($solicitud->info_pago)) {
            $solicitud->info_pago = json_decode($solicitud->info_pago, true);
        }

        return view('admin.solicitudes-pedidos.show', $this->getViewData(compact('solicitud')));
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

            // Verificar estado final
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

            // Restaurar stock si es rechazado
            if ($request->estado == 'rechazado') {
                $this->restoreStock($solicitud);
            }

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

    public function verComprobante($hash)
    {
        // Buscar la solicitud que contenga este archivo
        $solicitud = SolicitudPago::where('imagen_1', 'LIKE', "%{$hash}%")
            ->orWhere('imagen_2', 'LIKE', "%{$hash}%")
            ->orWhere('imagen_3', 'LIKE', "%{$hash}%")
            ->first();

        if (!$solicitud) {
            abort(404, 'Comprobante no encontrado');
        }

        $imagenData = $this->getImagePath($solicitud, $hash);

        if (!$imagenData || !file_exists($imagenData['path'])) {
            abort(404, 'El archivo no existe');
        }

        // Devolver la imagen
        return response()->file($imagenData['path'], [
            'Content-Type' => mime_content_type($imagenData['path']),
            'Content-Disposition' => 'inline; filename="comprobante_' . $solicitud->id . '.png"',
            'Cache-Control' => 'private, max-age=86400'
        ]);
    }
}
