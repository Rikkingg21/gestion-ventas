<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Carrito;
use App\Models\Moneda;
use App\Models\CarritoProducto;
use App\Helpers\MonedaHelper;
use App\Helpers\ImagenesHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helpers\GeoLocation;

class CarritoController extends Controller
{
    // Agregar producto al carrito
    public function agregarAlCarrito(Request $request, Producto $producto)
    {
        // Validar cantidad según tipo de producto
        if ($producto->esDigital()) {
            // Productos digitales: solo cantidad 1 y no se permite duplicado
            $cantidad = 1;

            // Validación específica para digitales: no se requiere validar cantidad
            $request->validate([
                // Solo validamos que exista el producto, la cantidad es fija
            ]);
        } else {
            // Productos físicos: validar cantidad normalmente
            $request->validate([
                'cantidad' => 'required|integer|min:1'
            ]);
            $cantidad = $request->cantidad;
        }

        // Validar stock si es producto físico
        if ($producto->esFisico()) {
            $stockActual = $producto->stock->cantidad ?? 0;
            if ($stockActual < $cantidad) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'No hay suficiente stock disponible.'], 400);
                }
                return back()->with('error', 'No hay suficiente stock disponible.');
            }
        }

        // Obtener o crear carrito actual
        $carrito = $this->obtenerCarritoActual();

        if (!$carrito) {
            $carrito = $this->crearNuevoCarrito();
        }

        // Calcular precios con descuento
        $precioFinalUSD = $producto->precioUSD;
        $precioFinalLocal = $producto->precioLocal;

        if ($producto->aplica_descuento && $producto->porcentaje_descuento > 0) {
            $descuento = $producto->porcentaje_descuento / 100;
            $precioFinalUSD = $producto->precioUSD * (1 - $descuento);
            $precioFinalLocal = $producto->precioLocal * (1 - $descuento);
        }

        // Verificar si el producto ya existe en el carrito
        $productoExistente = CarritoProducto::where('carrito_id', $carrito->id)
            ->where('producto_id', $producto->id)
            ->first();

        if ($productoExistente) {
            if ($producto->esDigital()) {
                // Productos digitales: no permitir duplicados
                if ($request->wantsJson()) {
                    return response()->json([
                        'error' => 'Este producto digital ya está en tu carrito. Solo puedes tener una unidad.'
                    ], 400);
                }
                return back()->with('error', 'Este producto digital ya está en tu carrito.');
            }

            // Productos físicos: actualizar cantidad
            $nuevaCantidad = $productoExistente->cantidad + $cantidad;

            // Verificar stock si es físico
            if ($producto->esFisico()) {
                $stockActual = $producto->stock->cantidad ?? 0;
                if ($stockActual < $nuevaCantidad) {
                    if ($request->wantsJson()) {
                        return response()->json(['error' => 'No puedes agregar más unidades de las disponibles en stock.'], 400);
                    }
                    return back()->with('error', 'No puedes agregar más unidades de las disponibles en stock.');
                }
            }

            $productoExistente->actualizarCantidad($nuevaCantidad);
            $message = 'Cantidad actualizada en el carrito';
        } else {
            // Crear nuevo producto en el carrito
            CarritoProducto::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $producto->id,
                'precio_adquirido_usd' => $precioFinalUSD,
                'precio_adquirido_local' => $precioFinalLocal,
                'aplica_descuento' => $producto->aplica_descuento,
                'porcentaje_descuento' => $producto->porcentaje_descuento,
                'cantidad' => $cantidad
            ]);
            $message = $producto->esDigital()
                ? 'Producto digital agregado al carrito'
                : 'Producto agregado al carrito';
        }

        // Actualizar sesión con el ID del carrito
        Session::put('carrito_id', $carrito->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'total_items' => $carrito->total_items,
                'total_usd' => $carrito->total_usd,
                'tipo_producto' => $producto->tipo_producto // Para información adicional
            ]);
        }

        return redirect()->route('productos.index')
            ->with('success', $message);
    }

    public function actualizarCantidad(Request $request, CarritoProducto $item)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1'
        ]);

        // Verificar si es producto digital
        if ($item->producto->esDigital()) {
            return response()->json([
                'error' => 'No puedes cambiar la cantidad de un producto digital. Solo se permite 1 unidad.'
            ], 400);
        }

        // Verificar stock si es físico
        if ($item->producto->esFisico()) {
            $stockActual = $item->producto->stock->cantidad ?? 0;
            if ($stockActual < $request->cantidad) {
                return response()->json([
                    'error' => 'No hay suficiente stock disponible.'
                ], 400);
            }
        }

        $item->actualizarCantidad($request->cantidad);

        // Obtener el carrito actualizado
        $carrito = $item->carrito;

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada',
            'total_items' => $carrito->total_items,
            'total_usd' => $carrito->total_usd
        ]);
    }

    // Ver carrito
    public function verCarrito(Request $request)
    {
        // Obtener el carrito actual
        $carrito = $this->obtenerCarritoActual();

        // MISMA LÓGICA DE MONEDA QUE EN INDEX
        $monedaActual = null;
        $codigoMonedaActual = 'USD'; // Default final siempre USD

        // 1. PRIORIDAD MÁXIMA: Moneda seleccionada en sesión (desde el layout)
        if (session()->has('moneda_seleccionada')) {
            $monedaSesion = Moneda::where('codigo_iso', session('moneda_seleccionada'))
                ->where('is_active', true)
                ->first();

            if ($monedaSesion) {
                $monedaActual = $monedaSesion;
                $codigoMonedaActual = $monedaSesion->codigo_iso;
            }
        }

        // 2. Si no hay moneda en sesión, verificar usuario autenticado
        if (!$monedaActual && Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
            $cliente = $user->client;

            if ($cliente && $cliente->pais) {
                // Buscar moneda por el país del cliente
                $monedaPorPais = Moneda::where('pais', $cliente->pais)
                    ->where('is_active', true)
                    ->first();

                if ($monedaPorPais) {
                    $monedaActual = $monedaPorPais;
                    $codigoMonedaActual = $monedaPorPais->codigo_iso;
                }
            }
        }

        // 3. Si no hay moneda (ni sesión, ni cliente autenticado), intentar con geolocalización
        if (!$monedaActual) {
            try {
                $geoInfo = GeoLocation::getCountryInfo();

                if (isset($geoInfo['country'])) {
                    // Buscar moneda por el país detectado
                    $monedaPorGeo = Moneda::where('pais', $geoInfo['country'])
                        ->where('is_active', true)
                        ->first();

                    if ($monedaPorGeo) {
                        $monedaActual = $monedaPorGeo;
                        $codigoMonedaActual = $monedaPorGeo->codigo_iso;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error al obtener geolocalización en verCarrito: ' . $e->getMessage());
            }
        }

        // 4. Si aún no hay moneda, usar USD como último recurso
        if (!$monedaActual) {
            $monedaActual = Moneda::where('codigo_iso', 'USD')
                ->where('is_active', true)
                ->first();

            // Si por alguna razón no existe USD en BD, usar la primera disponible
            if (!$monedaActual) {
                $monedaActual = Moneda::where('is_active', true)->first();
                if ($monedaActual) {
                    $codigoMonedaActual = $monedaActual->codigo_iso;
                } else {
                    // Fallback extremo - datos quemados (no debería pasar)
                    $monedaActual = (object)[
                        'id' => 0,
                        'codigo_iso' => 'USD',
                        'simbolo' => '$',
                        'nombre' => 'Dólar Americano'
                    ];
                }
            } else {
                $codigoMonedaActual = 'USD';
            }
        }

        // Si no hay carrito
        if (!$carrito) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'totales' => [
                        'total_usd' => 0,
                        'total_local' => 0,
                        'total_items' => 0,
                        'subtotal_usd' => 0,
                        'subtotal_local' => 0,
                        'moneda_actual' => [
                            'codigo' => $monedaActual->codigo_iso,
                            'simbolo' => $monedaActual->simbolo,
                            'nombre' => $monedaActual->nombre
                        ]
                    ],
                    'carrito_vacio' => true
                ]);
            }

            $totales = (object)[
                'total_usd' => 0,
                'total_local' => 0,
                'total_items' => 0,
                'subtotal_usd' => 0,
                'subtotal_local' => 0,
                'moneda_actual' => $monedaActual
            ];

            return view('client.carrito.index', compact('totales', 'monedaActual'));
        }

        // Cargar productos con sus relaciones
        $carrito->load(['productos' => function($query) {
            $query->with(['producto' => function($q) {
                $q->with(['categoria', 'precios.moneda', 'stock']);
            }]);
        }]);

        // Procesar items del carrito para mostrar en la moneda actual
        $itemsProcesados = $carrito->productos->map(function($item) use ($monedaActual) {
            $producto = $item->producto;

            // Buscar el precio del producto en la moneda actual para referencia
            $precioEnMonedaActual = null;
            if ($producto) {
                $precioEnMonedaActual = $producto->precios
                    ->where('moneda_id', $monedaActual->id)
                    ->where('is_active', true)
                    ->first();
            }

            // Obtener imagen principal
            $imagenPrincipal = null;
            if ($producto) {
                for ($i = 1; $i <= 5; $i++) {
                    $campo = 'imagen_url_' . $i;
                    if ($producto->$campo) {
                        $imagenPrincipal = $producto->getImageUrl($campo);
                        break;
                    }
                }
            }

            // Determinar qué precio mostrar según la moneda actual
            $precioUnitarioActual = $item->precio_adquirido_usd; // Default USD
            $subtotalActual = $item->subtotal_usd; // Default USD

            if ($monedaActual->codigo_iso == 'PEN') {
                $precioUnitarioActual = $item->precio_adquirido_local;
                $subtotalActual = $item->subtotal_local;
            }

            return (object)[
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $producto ? $producto->nombre : 'Producto no disponible',
                'cantidad' => $item->cantidad,
                'precio_unitario_usd' => $item->precio_adquirido_usd,
                'precio_unitario_local' => $item->precio_adquirido_local,
                'precio_unitario_actual' => $precioUnitarioActual,
                'precio_unitario_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($precioUnitarioActual, 2),
                'subtotal_usd' => $item->subtotal_usd,
                'subtotal_local' => $item->subtotal_local,
                'subtotal_actual' => $subtotalActual,
                'subtotal_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($subtotalActual, 2),
                'imagen' => $imagenPrincipal,
                'tipo_producto' => $producto ? $producto->tipo_producto : null,
                'sku' => $producto ? $producto->sku : null,
                'aplica_descuento' => $item->aplica_descuento,
                'porcentaje_descuento' => $item->porcentaje_descuento,
                'es_fisico' => $producto ? $producto->esFisico() : false,
                'es_digital' => $producto ? $producto->esDigital() : false,
                'stock_disponible' => $producto && $producto->esFisico() ? ($producto->stock->cantidad ?? 0) : null,
                'categoria' => $producto && $producto->categoria ? $producto->categoria->nombre : null,
                'url_recurso' => $producto && $producto->esDigital() ? $producto->url_recurso : null,
                'precio_referencia' => $precioEnMonedaActual ? [
                    'simbolo' => $monedaActual->simbolo,
                    'precio' => $precioEnMonedaActual->precio,
                    'formateado' => $monedaActual->simbolo . ' ' . number_format($precioEnMonedaActual->precio, 2)
                ] : null
            ];
        });

        // Calcular totales en la moneda actual
        $totalActual = $monedaActual->codigo_iso == 'PEN'
            ? $carrito->total_local
            : $carrito->total_usd;

        $totales = (object)[
            'total_usd' => $carrito->total_usd,
            'total_local' => $carrito->total_local,
            'total_actual' => $totalActual,
            'total_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($totalActual, 2),
            'total_items' => $carrito->total_items,
            'subtotal_usd' => $carrito->total_usd,
            'subtotal_local' => $carrito->total_local,
            'subtotal_actual' => $totalActual,
            'subtotal_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($totalActual, 2),
            'moneda_actual' => $monedaActual
        ];

        // Respuesta JSON para peticiones AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'items' => $itemsProcesados->map(function($item) {
                    return [
                        'id' => $item->id,
                        'producto_id' => $item->producto_id,
                        'nombre' => $item->nombre,
                        'cantidad' => $item->cantidad,
                        'precio_unitario_usd' => $item->precio_unitario_usd,
                        'precio_unitario_local' => $item->precio_unitario_local,
                        'precio_unitario_actual' => $item->precio_unitario_actual,
                        'precio_unitario_actual_formateado' => $item->precio_unitario_actual_formateado,
                        'subtotal_usd' => $item->subtotal_usd,
                        'subtotal_local' => $item->subtotal_local,
                        'subtotal_actual' => $item->subtotal_actual,
                        'subtotal_actual_formateado' => $item->subtotal_actual_formateado,
                        'imagen' => $item->imagen,
                        'tipo_producto' => $item->tipo_producto,
                        'sku' => $item->sku,
                        'aplica_descuento' => $item->aplica_descuento,
                        'porcentaje_descuento' => $item->porcentaje_descuento,
                        'es_fisico' => $item->es_fisico,
                        'es_digital' => $item->es_digital,
                        'stock_disponible' => $item->stock_disponible,
                        'categoria' => $item->categoria
                    ];
                }),
                'totales' => [
                    'total_usd' => $totales->total_usd,
                    'total_local' => $totales->total_local,
                    'total_actual' => $totales->total_actual,
                    'total_actual_formateado' => $totales->total_actual_formateado,
                    'total_items' => $totales->total_items,
                    'subtotal_usd' => $totales->subtotal_usd,
                    'subtotal_local' => $totales->subtotal_local,
                    'subtotal_actual' => $totales->subtotal_actual,
                    'subtotal_actual_formateado' => $totales->subtotal_actual_formateado,
                    'moneda_actual' => [
                        'codigo' => $monedaActual->codigo_iso,
                        'simbolo' => $monedaActual->simbolo,
                        'nombre' => $monedaActual->nombre
                    ]
                ],
                'carrito_id' => $carrito->id,
                'carrito_vacio' => $carrito->productos->isEmpty()
            ]);
        }

        return view('client.carrito.index', compact('itemsProcesados', 'totales', 'carrito', 'monedaActual'));
    }

    // Eliminar producto del carrito
    public function eliminarDelCarrito(Request $request, $productoId)
    {
        $carrito = $this->obtenerCarritoActual();

        if (!$carrito) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Carrito no encontrado'
                ], 404);
            }

            return redirect()->route('carrito.ver')
                ->with('error', 'Carrito no encontrado');
        }

        $item = CarritoProducto::where('carrito_id', $carrito->id)
            ->where('id', $productoId)
            ->first();

        if ($item) {
            $item->delete();
        }

        // Actualizar totales
        $carrito->refresh();

        // Si el carrito quedó vacío, actualizar su estado
        if ($carrito->productos()->count() === 0) {
            $carrito->update(['estado' => 'abandonado']);
            Session::forget('carrito_id');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'totales' => [
                    'total_usd' => $carrito->total_usd,
                    'total_local' => $carrito->total_local,
                    'total_items' => $carrito->total_items
                ],
                'carrito_vacio' => $carrito->productos()->count() === 0
            ]);
        }

        return redirect()->route('carrito.ver')
            ->with('success', 'Producto eliminado del carrito');
    }

    // Vaciar carrito
    public function vaciarCarrito()
    {
        $carrito = $this->obtenerCarritoActual();

        if ($carrito) {
            $carrito->vaciar();
            $carrito->update(['estado' => 'abandonado']);
            Session::forget('carrito_id');
        }

        return redirect()->route('carrito.ver')
            ->with('success', 'Carrito vaciado');
    }

    // Obtener el carrito actual basado en sesión, cliente o IP
    private function obtenerCarritoActual()
    {
        // Prioridad 1: Usuario autenticado con cliente (USANDO GUARD CLIENT)
        if (Auth::guard('client')->check() && Auth::guard('client')->user()->client) {
            $clienteId = Auth::guard('client')->user()->client->id;
            $carrito = Carrito::activo()
                ->where('cliente_id', $clienteId)
                ->first();

            if ($carrito) {
                return $carrito;
            }
        }

        // Prioridad 2: Carrito en sesión
        if (Session::has('carrito_id')) {
            $carrito = Carrito::activo()
                ->where('id', Session::get('carrito_id'))
                ->first();

            if ($carrito) {
                return $carrito;
            }
        }

        // Prioridad 3: Sesión actual
        $sessionId = Session::getId();
        if ($sessionId) {
            $carrito = Carrito::activo()
                ->where('session_id', $sessionId)
                ->first();

            if ($carrito) {
                // Actualizar la sesión con el ID del carrito
                Session::put('carrito_id', $carrito->id);
                return $carrito;
            }
        }

        // Prioridad 4: IP (último recurso)
        $ip = request()->ip();
        $carrito = Carrito::activo()
            ->where('ip_address', $ip)
            ->whereNull('session_id')
            ->whereNull('cliente_id')
            ->latest()
            ->first();

        if ($carrito) {
            // Actualizar la sesión con el ID del carrito
            Session::put('carrito_id', $carrito->id);
            return $carrito;
        }
        return null;
    }

    // Crear un nuevo carrito
    private function crearNuevoCarrito()
    {
        $data = [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'estado' => 'activo'
        ];

        // Si el usuario está autenticado con guard client y tiene cliente
        if (Auth::guard('client')->check() && Auth::guard('client')->user()->client) {
            $data['cliente_id'] = Auth::guard('client')->user()->client->id;
        } else {
            $data['session_id'] = Session::getId();
        }

        $carrito = Carrito::create($data);
        Session::put('carrito_id', $carrito->id);

        return $carrito;
    }

    // Migrar carrito de sesión a cliente cuando inicia sesión
    public function migrarCarritoSesionACliente($oldSessionId = null)
    {
        // Verificar autenticación con guard client
        $user = Auth::guard('client')->user();

        if (!Auth::guard('client')->check() || !$user->client) {
            return;
        }

        // Determinar qué session_id usar para buscar el carrito
        $sessionId = $oldSessionId ?? Session::getId();
        $clienteId = $user->client->id;

        // Buscar carrito de sesión
        $carritoSesion = Carrito::activo()
            ->where('session_id', $sessionId)
            ->first();

        // Si no encuentra con el old_session_id, intentar con el actual
        if (!$carritoSesion) {
            $carritoSesion = Carrito::activo()
                ->where('session_id', Session::getId())
                ->first();
        }

        // Si no hay carrito de sesión o está vacío, salir
        if (!$carritoSesion || $carritoSesion->productos()->count() === 0) {
            if ($carritoSesion && $carritoSesion->productos()->count() === 0) {
                $carritoSesion->update(['estado' => 'abandonado']);
                Session::forget('carrito_id');
            }
            return;
        }

        // Buscar carrito del cliente
        $carritoCliente = Carrito::activo()
            ->where('cliente_id', $clienteId)
            ->first();

        if ($carritoCliente) {
            // Fusionar productos del carrito de sesión al del cliente
            foreach ($carritoSesion->productos as $producto) {
                $productoExistente = CarritoProducto::where('carrito_id', $carritoCliente->id)
                    ->where('producto_id', $producto->producto_id)
                    ->first();

                if ($productoExistente) {
                    // Sumar cantidades
                    $nuevaCantidad = $productoExistente->cantidad + $producto->cantidad;

                    // Validar stock si es físico
                    if ($producto->producto && $producto->producto->esFisico()) {
                        $stockDisponible = $producto->producto->stock->cantidad ?? 0;
                        if ($nuevaCantidad > $stockDisponible) {
                            $nuevaCantidad = $stockDisponible;
                        }
                    }

                    $productoExistente->actualizarCantidad($nuevaCantidad);
                    $producto->delete(); // Eliminar el producto del carrito de sesión
                } else {
                    // Reasignar producto al carrito del cliente
                    $producto->carrito_id = $carritoCliente->id;
                    $producto->save();
                }
            }

            // Si el carrito de sesión quedó vacío, eliminarlo
            if ($carritoSesion->productos()->count() === 0) {
                $carritoSesion->update(['estado' => 'abandonado']);
                $carritoSesion->delete();
                Session::put('carrito_id', $carritoCliente->id);
            }
        } else {
            // Asignar carrito de sesión al cliente
            $carritoSesion->update([
                'cliente_id' => $clienteId,
                'session_id' => null
            ]);
            Session::put('carrito_id', $carritoSesion->id);
        }

        Session::save();
    }
}
