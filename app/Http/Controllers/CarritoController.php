<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Carrito;
use App\Models\CarritoProducto;
use App\Models\Moneda;
use App\Helpers\MonedaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    // Obtener los datos del sidebar del carrito
    public static function carritoSidebar()
    {
        try {
            $carrito = self::cargarCarrito();
            $monedaActual = MonedaHelper::getMonedaActual();

            if (!$carrito || $carrito->estaVacio()) {
                return [
                    'totalItems' => 0,
                    'totalLocal' => 0,
                    'totalUsd' => 0,
                    'totalActual' => 0,
                    'moneda_actual' => $monedaActual,
                    'carrito_vacio' => true,
                    'acepta_pagos' => $monedaActual->aceptar_pagos ?? false
                ];
            }

            $totalItems = $carrito->total_items;
            $totalLocal = $carrito->total_local;
            $totalUsd = $carrito->total_usd;

            $aceptaPagos = $monedaActual->aceptar_pagos ?? false;

            if ($aceptaPagos) {
                if ($carrito->moneda_id == $monedaActual->id) {
                    $totalActual = $totalLocal;
                } else {
                    $totalActual = $totalUsd;
                }
            } else {
                $totalActual = $totalUsd;
            }

            return [
                'totalItems' => $totalItems,
                'totalLocal' => $totalLocal,
                'totalUsd' => $totalUsd,
                'totalActual' => $totalActual,
                'moneda_actual' => $monedaActual,
                'carrito_vacio' => false,
                'carrito' => $carrito,
                'acepta_pagos' => $aceptaPagos
            ];

        } catch (\Exception $e) {
            $monedaActual = MonedaHelper::getMonedaActual();
            $monedaActual->aceptar_pagos = $monedaActual->aceptar_pagos ?? false;

            return [
                'totalItems' => 0,
                'totalLocal' => 0,
                'totalUsd' => 0,
                'totalActual' => 0,
                'moneda_actual' => $monedaActual,
                'carrito_vacio' => true,
                'error' => true,
                'acepta_pagos' => $monedaActual->aceptar_pagos
            ];
        }
    }

    // Cargar el carrito actual sin crear uno nuevo
    public static function cargarCarrito()
    {
        try {
            // Prioridad 1: Usuario autenticado
            if (Auth::guard('client')->check() && Auth::guard('client')->user()->client) {
                $clienteId = Auth::guard('client')->user()->client->id;

                $carrito = Carrito::activo()
                    ->where('cliente_id', $clienteId)
                    ->first();

                if ($carrito) {
                    $carrito->update([
                        'session_id' => Session::getId(),
                        'user_agent' => request()->userAgent(),
                        'ip_address' => request()->ip()
                    ]);
                    Session::put('carrito_id', $carrito->id);
                    return $carrito;
                }
            }

            // Prioridad 2: Carrito en sesión
            if (Session::has('carrito_id')) {
                $carritoId = Session::get('carrito_id');

                $carrito = Carrito::activo()
                    ->where('id', $carritoId)
                    ->first();

                if ($carrito) {
                    if (Auth::guard('client')->check() && Auth::guard('client')->user()->client && !$carrito->cliente_id) {
                        $carrito->update([
                            'cliente_id' => Auth::guard('client')->user()->client->id,
                            'session_id' => null
                        ]);
                    } else {
                        $carrito->update([
                            'session_id' => Session::getId(),
                            'user_agent' => request()->userAgent(),
                            'ip_address' => request()->ip()
                        ]);
                    }
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
                    Session::put('carrito_id', $carrito->id);
                    $carrito->update([
                        'user_agent' => request()->userAgent(),
                        'ip_address' => request()->ip()
                    ]);
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
                $carrito->update([
                    'session_id' => Session::getId(),
                    'user_agent' => request()->userAgent()
                ]);
                Session::put('carrito_id', $carrito->id);
                return $carrito;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    // Crear un nuevo carrito
    private static function crearCarrito()
    {
        $monedaActual = MonedaHelper::getMonedaActual();

        $data = [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'estado' => 'activo',
            'moneda_id' => $monedaActual->id ?? null
        ];

        if (Auth::guard('client')->check() && Auth::guard('client')->user()->client) {
            $data['cliente_id'] = Auth::guard('client')->user()->client->id;
        } else {
            $data['session_id'] = Session::getId();
        }

        $carrito = Carrito::create($data);
        Session::put('carrito_id', $carrito->id);

        return $carrito;
    }

    // Obtener o crear carrito
    private static function obtenerOCrearCarrito()
    {
        $carrito = self::cargarCarrito();

        if (!$carrito) {
            $carrito = self::crearCarrito();
        }

        return $carrito;
    }

    // Vaciar carrito manteniéndolo activo
    private static function vaciarCarritoMantenerActivo(Carrito $carrito)
    {
        return $carrito->vaciar();
    }

    // Agregar producto al carrito
    public function agregarAlCarrito(Request $request, Producto $producto)
    {
        try {
            // Validar cantidad según tipo de producto
            if ($producto->esDigital()) {
                $cantidad = 1;
            } else {
                $request->validate([
                    'cantidad' => 'required|integer|min:1'
                ]);
                $cantidad = $request->cantidad;
            }

            // Validar stock si es producto físico
            if ($producto->esFisico()) {
                $stockActual = $producto->stock->cantidad ?? 0;
                if ($stockActual < $cantidad) {
                    $errorMsg = 'No hay suficiente stock disponible.';
                    if ($request->wantsJson()) {
                        return response()->json(['error' => $errorMsg], 400);
                    }
                    return back()->with('error', $errorMsg);
                }
            }

            // Obtener o crear carrito actual
            $carrito = self::obtenerOCrearCarrito();

            // Obtener información de precios
            $monedaActual = MonedaHelper::getMonedaActual();

            // IMPORTANTE: Obtener precio en USD (moneda base) y precio en la moneda actual
            $monedaUSD = Moneda::where('codigo_iso', 'USD')->first();

            // Precio en USD (siempre en dólares)
            $precioInfoUSD = MonedaHelper::getPrecioProductoEnMoneda($producto, $monedaUSD->id);
            $precioUSD = $precioInfoUSD['precio_con_descuento'];

            // Precio en la moneda actual (PEN, COP, etc.)
            $precioInfoLocal = MonedaHelper::getPrecioProductoEnMoneda($producto, $monedaActual->id);
            $precioLocal = $precioInfoLocal['precio_con_descuento'];

            // Verificar si el producto ya existe en el carrito
            $productoExistente = CarritoProducto::where('carrito_id', $carrito->id)
                ->where('producto_id', $producto->id)
                ->first();

            if ($productoExistente) {
                if ($producto->esDigital()) {
                    $errorMsg = 'Este producto digital ya está en tu carrito. Solo puedes tener una unidad.';
                    if ($request->wantsJson()) {
                        return response()->json(['error' => $errorMsg], 400);
                    }
                    return back()->with('error', $errorMsg);
                }

                // Productos físicos: actualizar cantidad
                $nuevaCantidad = $productoExistente->cantidad + $cantidad;

                // Verificar stock
                if ($producto->esFisico()) {
                    $stockActual = $producto->stock->cantidad ?? 0;
                    if ($stockActual < $nuevaCantidad) {
                        $errorMsg = 'No puedes agregar más unidades de las disponibles en stock.';
                        if ($request->wantsJson()) {
                            return response()->json(['error' => $errorMsg], 400);
                        }
                        return back()->with('error', $errorMsg);
                    }
                }

                $productoExistente->actualizarCantidad($nuevaCantidad);
                $message = 'Cantidad actualizada en el carrito';
            } else {
                // Crear nuevo producto en el carrito
                CarritoProducto::create([
                    'carrito_id' => $carrito->id,
                    'producto_id' => $producto->id,
                    'precio_adquirido_usd' => $precioUSD,
                    'precio_adquirido_local' => $precioLocal,
                    'aplica_descuento' => $producto->aplica_descuento,
                    'porcentaje_descuento' => $producto->porcentaje_descuento,
                    'cantidad' => $cantidad
                ]);
                $message = $producto->esDigital()
                    ? 'Producto digital agregado al carrito'
                    : 'Producto agregado al carrito';
            }

            Session::put('carrito_id', $carrito->id);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'total_items' => $carrito->total_items,
                    'total_local' => $carrito->total_local,
                    'total_usd' => $carrito->total_usd,
                    'tipo_producto' => $producto->tipo_producto,
                    'moneda_actual' => [
                        'codigo' => $monedaActual->codigo_iso,
                        'simbolo' => $monedaActual->simbolo,
                        'nombre' => $monedaActual->nombre
                    ]
                ]);
            }

            return redirect()->route('producto.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            $errorMsg = 'Error al agregar producto al carrito.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $errorMsg], 500);
            }
            return back()->with('error', $errorMsg);
        }
    }

    // Actualizar cantidad de un producto en el carrito
    public function actualizarCantidad(Request $request, CarritoProducto $item)
    {
        try {
            $request->validate([
                'cantidad' => 'required|integer|min:1'
            ]);

            if ($item->producto->esDigital()) {
                return response()->json([
                    'error' => 'No puedes cambiar la cantidad de un producto digital. Solo se permite 1 unidad.'
                ], 400);
            }

            if ($item->producto->esFisico()) {
                $stockActual = $item->producto->stock->cantidad ?? 0;
                if ($stockActual < $request->cantidad) {
                    return response()->json([
                        'error' => 'No hay suficiente stock disponible.'
                    ], 400);
                }
            }

            $item->actualizarCantidad($request->cantidad);
            $carrito = $item->carrito;
            $monedaActual = MonedaHelper::getMonedaActual();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada',
                'total_items' => $carrito->total_items,
                'total_local' => $carrito->total_local,
                'total_usd' => $carrito->total_usd,
                'moneda_actual' => [
                    'codigo' => $monedaActual->codigo_iso,
                    'simbolo' => $monedaActual->simbolo,
                    'nombre' => $monedaActual->nombre
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la cantidad.'
            ], 500);
        }
    }

    // Ver carrito
    public function verCarrito(Request $request)
    {
        try {
            $carrito = self::cargarCarrito();
            $monedaActual = MonedaHelper::getMonedaActual();
            $aceptaPagos = $monedaActual->aceptar_pagos ?? false;

            // Obtener la moneda que se usará para cobrar (USD si no acepta pagos)
            $monedaACobrar = $aceptaPagos ? $monedaActual : Moneda::where('codigo_iso', 'USD')->first();

            if (!$carrito || $carrito->estaVacio()) {
                $totales = $this->getTotalesVacios($monedaActual, $aceptaPagos, $monedaACobrar);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'items' => [],
                        'totales' => $totales,
                        'carrito_id' => null,
                        'carrito_vacio' => true,
                        'acepta_pagos' => $aceptaPagos
                    ]);
                }

                return view('client.carrito.index', compact('totales', 'monedaActual', 'aceptaPagos', 'monedaACobrar'));
            }

            $carrito->load(['productos' => function($query) {
                $query->with(['producto' => function($q) {
                    $q->with(['categoria', 'precios.moneda', 'stock']);
                }]);
            }]);

            $itemsProcesados = $this->procesarItemsCarrito($carrito, $monedaActual, $aceptaPagos);
            $totales = $this->calcularTotalesCarrito($carrito, $monedaActual, $aceptaPagos, $monedaACobrar);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'items' => $itemsProcesados,
                    'totales' => $totales,
                    'carrito_id' => $carrito->id,
                    'carrito_vacio' => $carrito->estaVacio(),
                    'acepta_pagos' => $aceptaPagos
                ]);
            }

            return view('client.carrito.index', compact('itemsProcesados', 'totales', 'carrito', 'monedaActual', 'aceptaPagos', 'monedaACobrar'));

        } catch (\Exception $e) {
            $monedaActual = MonedaHelper::getMonedaActual();
            $aceptaPagos = $monedaActual->aceptar_pagos ?? false;
            $monedaACobrar = $aceptaPagos ? $monedaActual : Moneda::where('codigo_iso', 'USD')->first();
            $totales = $this->getTotalesVacios($monedaActual, $aceptaPagos, $monedaACobrar);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error al cargar el carrito'
                ], 500);
            }

            return view('client.carrito.index', compact('totales', 'monedaActual', 'aceptaPagos', 'monedaACobrar'))
                ->with('error', 'Error al cargar el carrito');
        }
    }

    // Eliminar producto del carrito
    public function eliminarDelCarrito(Request $request, $productoId)
    {
        try {
            $carrito = self::cargarCarrito();

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

            $carrito->refresh();
            $monedaActual = MonedaHelper::getMonedaActual();

            $totalActual = $carrito->total_usd;
            if ($carrito->moneda_id == $monedaActual->id) {
                $totalActual = $carrito->total_local;
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado del carrito',
                    'totales' => [
                        'total_usd' => $carrito->total_usd,
                        'total_local' => $carrito->total_local,
                        'total_actual' => $totalActual,
                        'total_items' => $carrito->total_items,
                        'moneda_actual' => [
                            'codigo' => $monedaActual->codigo_iso,
                            'simbolo' => $monedaActual->simbolo,
                            'nombre' => $monedaActual->nombre
                        ]
                    ],
                    'carrito_vacio' => $carrito->estaVacio()
                ]);
            }

            return redirect()->route('carrito.ver')
                ->with('success', 'Producto eliminado del carrito');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Error al eliminar el producto'], 500);
            }
            return redirect()->route('carrito.ver')
                ->with('error', 'Error al eliminar el producto');
        }
    }

    // Vaciar carrito completamente
    public function vaciarCarrito()
    {
        try {
            $carrito = self::cargarCarrito();

            if ($carrito && !$carrito->estaVacio()) {
                self::vaciarCarritoMantenerActivo($carrito);
                Session::put('carrito_id', $carrito->id);
            }

            return redirect()->route('carrito.ver')
                ->with('success', 'Carrito vaciado correctamente');

        } catch (\Exception $e) {
            return redirect()->route('carrito.ver')
                ->with('error', 'Error al vaciar el carrito');
        }
    }

    // Migrar carrito de sesión a cliente cuando inicia sesión
    public function migrarCarritoSesionACliente($oldSessionId = null)
    {
        try {
            $user = Auth::guard('client')->user();

            if (!Auth::guard('client')->check() || !$user->client) {
                return;
            }

            $sessionId = $oldSessionId ?? Session::getId();
            $clienteId = $user->client->id;

            $carritoSesion = Carrito::activo()
                ->where('session_id', $sessionId)
                ->first();

            if (!$carritoSesion) {
                $carritoSesion = Carrito::activo()
                    ->where('session_id', Session::getId())
                    ->first();
            }

            if (!$carritoSesion || $carritoSesion->estaVacio()) {
                if ($carritoSesion && $carritoSesion->estaVacio()) {
                    Session::forget('carrito_id');
                }
                return;
            }

            $carritoCliente = Carrito::activo()
                ->where('cliente_id', $clienteId)
                ->first();

            if ($carritoCliente) {
                $this->fusionarCarritos($carritoSesion, $carritoCliente);
                if ($carritoSesion->estaVacio()) {
                    $carritoSesion->delete();
                    Session::put('carrito_id', $carritoCliente->id);
                }
            } else {
                $carritoSesion->update([
                    'cliente_id' => $clienteId,
                    'session_id' => null
                ]);
                Session::put('carrito_id', $carritoSesion->id);
            }

            Session::save();

        } catch (\Exception $e) {
            // Error silencioso
        }
    }

    // Métodos privados auxiliares
    private function procesarItemsCarrito($carrito, $monedaActual, $aceptaPagos)
    {
        return $carrito->productos->map(function($item) use ($carrito, $monedaActual, $aceptaPagos) {
            $producto = $item->producto;
            $precioInfo = $producto ? MonedaHelper::getPrecioProductoEnMoneda($producto, $monedaActual->id) : null;

            // Calcular el precio a mostrar en la moneda actual
            if ($aceptaPagos) {
                // Si la moneda actual acepta pagos, mostrar el precio en la moneda local
                $precioMostrar = $item->precio_adquirido_local;
            } else {
                // Si NO acepta pagos, mostrar el precio_local (ya está en la moneda actual)
                $precioMostrar = $item->precio_adquirido_local;
            }

            $itemData = [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $producto ? $producto->nombre : 'Producto no disponible',
                'cantidad' => $item->cantidad,
                'precio_unitario_usd' => $item->precio_adquirido_usd,
                'precio_unitario_local' => $item->precio_adquirido_local,
                'precio_mostrar' => $precioMostrar,
                'imagen' => $this->obtenerImagenPrincipal($producto),
                'tipo_producto' => $producto ? $producto->tipo_producto : null,
                'sku' => $producto ? $producto->sku : null,
                'aplica_descuento' => $item->aplica_descuento,
                'porcentaje_descuento' => $item->porcentaje_descuento,
                'es_fisico' => $producto ? $producto->esFisico() : false,
                'es_digital' => $producto ? $producto->esDigital() : false,
                'stock_disponible' => $producto && $producto->esFisico() ? ($producto->stock->cantidad ?? 0) : null,
                'categoria' => $producto && $producto->categoria ? $producto->categoria->nombre : null,
                'url_recurso' => $producto && $producto->esDigital() ? $producto->url_recurso : null,
                'precio_referencia' => $precioInfo ? [
                    'simbolo' => $precioInfo['moneda']['simbolo'],
                    'precio' => $precioInfo['precio_con_descuento'],
                    'formateado' => $precioInfo['precio_con_descuento_formateado']
                ] : null,
                'acepta_pagos_moneda_actual' => $aceptaPagos
            ];

            return $itemData;
        })->toArray();
    }

    private function calcularTotalesCarrito($carrito, $monedaActual, $aceptaPagos, $monedaACobrar)
    {
        // Calcular total actual para mostrar (siempre en la moneda actual para visualización)
        if ($aceptaPagos) {
            // Si la moneda actual acepta pagos, mostrar el total en esa moneda
            $totalActual = $carrito->moneda_id == $monedaActual->id
                ? $carrito->total_local
                : $carrito->total_usd;
        } else {
            // Si la moneda actual NO acepta pagos, mostrar el total_local (ya está en la moneda actual)
            $totalActual = $carrito->total_local;
        }

        return [
            'total_usd' => $carrito->total_usd,
            'total_local' => $carrito->total_local,
            'total_actual' => $totalActual,
            'total_items' => $carrito->total_items,
            'moneda_actual' => [
                'id' => $monedaActual->id,
                'codigo' => $monedaActual->codigo_iso,
                'simbolo' => $monedaActual->simbolo,
                'nombre' => $monedaActual->nombre,
                'tasa_cambio_usd' => $monedaActual->tasa_cambio_usd ?? 1,
                'acepta_pagos' => $aceptaPagos
            ],
            'acepta_pagos' => $aceptaPagos,
            'moneda_a_cobrar' => $aceptaPagos ? null : [
                'id' => $monedaACobrar->id,
                'codigo' => $monedaACobrar->codigo_iso,
                'simbolo' => $monedaACobrar->simbolo,
                'nombre' => $monedaACobrar->nombre,
                'tasa_cambio_usd' => $monedaACobrar->tasa_cambio_usd ?? 1,
                'acepta_pagos' => $monedaACobrar->aceptar_pagos ?? true
            ]
        ];
    }

    private function getTotalesVacios($monedaActual, $aceptaPagos, $monedaACobrar)
    {
        return [
            'total_usd' => 0,
            'total_local' => 0,
            'total_actual' => 0,
            'total_items' => 0,
            'moneda_actual' => [
                'id' => $monedaActual->id,
                'codigo' => $monedaActual->codigo_iso,
                'simbolo' => $monedaActual->simbolo,
                'nombre' => $monedaActual->nombre,
                'tasa_cambio_usd' => $monedaActual->tasa_cambio_usd ?? 1,
                'acepta_pagos' => $aceptaPagos
            ],
            'acepta_pagos' => $aceptaPagos,
            'moneda_a_cobrar' => $aceptaPagos ? null : [
                'id' => $monedaACobrar->id,
                'codigo' => $monedaACobrar->codigo_iso,
                'simbolo' => $monedaACobrar->simbolo,
                'nombre' => $monedaACobrar->nombre,
                'tasa_cambio_usd' => $monedaACobrar->tasa_cambio_usd ?? 1,
                'acepta_pagos' => $monedaACobrar->aceptar_pagos ?? true
            ]
        ];
    }

    private function obtenerImagenPrincipal($producto)
    {
        if (!$producto) return null;

        for ($i = 1; $i <= 5; $i++) {
            $campo = 'imagen_url_' . $i;
            if ($producto->$campo) {
                return $producto->getImageUrl($campo);
            }
        }
        return null;
    }

    private function fusionarCarritos($carritoOrigen, $carritoDestino)
    {
        foreach ($carritoOrigen->productos as $producto) {
            $productoExistente = CarritoProducto::where('carrito_id', $carritoDestino->id)
                ->where('producto_id', $producto->producto_id)
                ->first();

            if ($productoExistente) {
                $nuevaCantidad = $productoExistente->cantidad + $producto->cantidad;

                if ($producto->producto && $producto->producto->esFisico()) {
                    $stockDisponible = $producto->producto->stock->cantidad ?? 0;
                    if ($nuevaCantidad > $stockDisponible) {
                        $nuevaCantidad = $stockDisponible;
                    }
                }

                $productoExistente->actualizarCantidad($nuevaCantidad);
                $producto->delete();
            } else {
                $producto->carrito_id = $carritoDestino->id;
                $producto->save();
            }
        }
    }
}
