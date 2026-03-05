<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\CuponUsado;
use App\Models\SolicitudPago;
use App\Models\SolicitudPagoEstado;
use App\Models\Moneda;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\GeoLocation;

class PayController extends Controller
{
    public function index()
    {
        // Obtener el usuario autenticado
        $user = Auth::guard('client')->user();

        if (!$user) {
            return redirect()->route('client.login')
                ->with('error', 'Debes iniciar sesión para continuar')
                ->with('redirect_to', route('checkout.index'));
        }

        // Obtener el cliente asociado al usuario
        $cliente = $user->client;

        if (!$cliente) {
            return redirect()->route('client.login')
                ->with('error', 'No tienes un perfil de cliente asociado');
        }

        // MISMA LÓGICA DE MONEDA QUE EN PRODUCTOS
        $monedaActual = null;
        $codigoMonedaActual = 'USD'; // Default

        // 1. PRIORIDAD MÁXIMA: Moneda seleccionada en sesión
        if (session()->has('moneda_seleccionada')) {
            $monedaSesion = Moneda::where('codigo_iso', session('moneda_seleccionada'))
                ->where('is_active', true)
                ->first();

            if ($monedaSesion) {
                $monedaActual = $monedaSesion;
                $codigoMonedaActual = $monedaSesion->codigo_iso;
            }
        }

        // 2. Si no hay moneda en sesión, usar país del cliente
        if (!$monedaActual && $cliente && $cliente->pais) {
            $monedaPorPais = Moneda::where('pais', $cliente->pais)
                ->where('is_active', true)
                ->first();

            if ($monedaPorPais) {
                $monedaActual = $monedaPorPais;
                $codigoMonedaActual = $monedaPorPais->codigo_iso;
            }
        }

        // 3. Si no hay moneda, intentar con geolocalización
        if (!$monedaActual) {
            try {
                $geoInfo = GeoLocation::getCountryInfo();
                if (isset($geoInfo['country'])) {
                    $monedaPorGeo = Moneda::where('pais', $geoInfo['country'])
                        ->where('is_active', true)
                        ->first();

                    if ($monedaPorGeo) {
                        $monedaActual = $monedaPorGeo;
                        $codigoMonedaActual = $monedaPorGeo->codigo_iso;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error en geolocalización checkout: ' . $e->getMessage());
            }
        }

        // 4. USD como fallback
        if (!$monedaActual) {
            $monedaActual = Moneda::where('codigo_iso', 'USD')
                ->where('is_active', true)
                ->first();

            if (!$monedaActual) {
                $monedaActual = (object)[
                    'id' => 0,
                    'codigo_iso' => 'USD',
                    'simbolo' => '$',
                    'nombre' => 'Dólar Americano'
                ];
            }
        }

        // Obtener carrito activo del cliente
        $carrito = Carrito::activo()
            ->with(['productos' => function($query) {
                $query->with(['producto' => function($q) {
                    $q->with(['categoria', 'precios.moneda']);
                }]);
            }])
            ->where('cliente_id', $cliente->id)
            ->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return redirect()->route('carrito.ver')
                ->with('error', 'Tu carrito está vacío');
        }

        // Verificar si hay un cupón aplicado en la sesión
        $cuponAplicado = null;
        $descuentoAplicado = 0;
        $totalConDescuento = 0;

        if (session()->has('cupon_aplicado')) {
            $cuponAplicado = session('cupon_aplicado');
        }

        // Procesar items para mostrar en la moneda actual
        $itemsProcesados = $carrito->productos->map(function($item) use ($monedaActual) {
            $producto = $item->producto;

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
                'imagen' => $this->getImagenProducto($producto),
                'tipo_producto' => $producto ? $producto->tipo_producto : null,
                'es_fisico' => $producto ? $producto->esFisico() : false,
                'es_digital' => $producto ? $producto->esDigital() : false,
                'aplica_descuento' => $item->aplica_descuento,
                'porcentaje_descuento' => $item->porcentaje_descuento,
            ];
        });

        // Calcular totales base sin descuento de cupón
        $subtotalBaseActual = $monedaActual->codigo_iso == 'PEN'
            ? $carrito->total_local
            : $carrito->total_usd;

        $subtotalBaseUSD = $carrito->total_usd;
        $subtotalBaseLocal = $carrito->total_local;

        // Aplicar descuento del cupón si existe
        $descuentoAplicado = 0;
        $totalConDescuento = $subtotalBaseActual;

        if ($cuponAplicado) {
            if ($cuponAplicado['tipo'] == 'porcentaje') {
                $descuentoAplicado = $subtotalBaseActual * ($cuponAplicado['valor'] / 100);
            } else {
                $descuentoAplicado = $cuponAplicado['valor'];
                // Asegurar que el descuento no sea mayor al subtotal
                $descuentoAplicado = min($descuentoAplicado, $subtotalBaseActual);
            }
            $totalConDescuento = $subtotalBaseActual - $descuentoAplicado;
        }

        $totales = (object)[
            'subtotal_usd' => $subtotalBaseUSD,
            'subtotal_local' => $subtotalBaseLocal,
            'subtotal_actual' => $subtotalBaseActual,
            'subtotal_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($subtotalBaseActual, 2),
            'descuento_aplicado' => $descuentoAplicado,
            'descuento_aplicado_formateado' => $monedaActual->simbolo . ' ' . number_format($descuentoAplicado, 2),
            'total_actual' => $totalConDescuento,
            'total_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($totalConDescuento, 2),
            'total_usd' => $carrito->total_usd, // Esto debería recalcularse con el descuento
            'total_items' => $carrito->total_items,
            'moneda_actual' => $monedaActual,
            'cupon_aplicado' => $cuponAplicado
        ];

        // Obtener todas las monedas activas para el selector
        $monedasDisponibles = Moneda::where('is_active', true)
            ->orderBy('codigo_iso')
            ->get();

        return view('client.checkout.index', compact(
            'itemsProcesados',
            'totales',
            'carrito',
            'user',
            'cliente',
            'monedaActual',
            'monedasDisponibles'
        ));
    }

    private function getImagenProducto($producto)
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

    // Validar cupón
    public function validarCupon(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50'
        ]);

        $user = Auth::guard('client')->user();
        if (!$user || !$user->client) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para aplicar un cupón'
            ], 401);
        }

        $clienteId = $user->client->id;

        // Buscar cupón activo por código
        $cupon = Cupon::where('codigo', $request->codigo)
            ->where('is_active', true)
            ->first();

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupón no válido o inexistente'
            ]);
        }

        // Validar fechas
        if ($cupon->fecha_fin && $cupon->fecha_fin->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Este cupón ha expirado'
            ]);
        }

        if ($cupon->fecha_inicio && $cupon->fecha_inicio->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'Este cupón aún no está disponible'
            ]);
        }

        // Validar stock
        if ($cupon->stok_actual <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este cupón ya no tiene usos disponibles'
            ]);
        }

        // Verificar si el cliente ya usó este cupón
        $yaUsado = CuponUsado::where('cupon_id', $cupon->id)
            ->where('cliente_id', $clienteId)
            ->exists();

        if ($yaUsado) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has utilizado este cupón anteriormente'
            ]);
        }

        // Obtener el carrito actual
        $carrito = Carrito::activo()
            ->with(['productos.producto'])
            ->where('cliente_id', $clienteId)
            ->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes productos en el carrito'
            ]);
        }

        // Obtener moneda actual
        $monedaActual = $this->obtenerMonedaActual();

        // Validar que el cupón sea aplicable a la moneda actual
        if ($cupon->moneda_id && $cupon->moneda_id != $monedaActual->id) {
            $monedaCupon = Moneda::find($cupon->moneda_id);
            return response()->json([
                'success' => false,
                'message' => 'Este cupón solo aplica para compras en ' . ($monedaCupon ? $monedaCupon->nombre : 'otra moneda')
            ]);
        }

        // Calcular subtotal en la moneda actual
        $subtotalActual = $monedaActual->codigo_iso == 'PEN'
            ? $carrito->total_local
            : $carrito->total_usd;

        // Calcular descuento según el tipo de cupón
        $descuento = 0;
        $tipoDescuento = null;
        $valorDescuento = 0;

        // Verificar si tiene porcentaje de descuento (mayor a 0 y no nulo)
        if (!is_null($cupon->porcentaje_descuento) && $cupon->porcentaje_descuento > 0) {
            // Cupón de porcentaje
            $descuento = $subtotalActual * ($cupon->porcentaje_descuento / 100);
            $tipoDescuento = 'porcentaje';
            $valorDescuento = $cupon->porcentaje_descuento;
        }
        // Verificar si tiene monto de descuento (mayor a 0 y no nulo)
        elseif (!is_null($cupon->descuento_monto) && $cupon->descuento_monto > 0) {
            // Cupón de monto fijo
            $descuento = $cupon->descuento_monto;
            $tipoDescuento = 'monto_fijo';
            $valorDescuento = $cupon->descuento_monto;

            // Asegurar que el descuento no sea mayor al subtotal
            $descuento = min($descuento, $subtotalActual);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'El cupón no tiene un descuento válido configurado'
            ]);
        }

        // Guardar cupón en sesión
        session(['cupon_aplicado' => [
            'id' => $cupon->id,
            'codigo' => $cupon->codigo,
            'nombre' => $cupon->nombre,
            'tipo' => $tipoDescuento,
            'valor' => $valorDescuento,
            'descuento' => $descuento,
            'moneda_id' => $cupon->moneda_id,
            'moneda_codigo' => $cupon->moneda ? $cupon->moneda->codigo_iso : null
        ]]);

        // Calcular nuevo total
        $totalConDescuento = $subtotalActual - $descuento;

        // Preparar mensaje según el tipo de cupón
        $mensajeDescuento = '';
        if ($tipoDescuento == 'porcentaje') {
            $mensajeDescuento = "{$valorDescuento}% de descuento";
        } else {
            $mensajeDescuento = "{$monedaActual->simbolo} " . number_format($valorDescuento, 2) . " de descuento";
        }

        return response()->json([
            'success' => true,
            'message' => "Cupón aplicado correctamente. ¡{$mensajeDescuento}!",
            'cupon' => [
                'id' => $cupon->id,
                'codigo' => $cupon->codigo,
                'nombre' => $cupon->nombre,
                'descuento_formateado' => $monedaActual->simbolo . ' ' . number_format($descuento, 2),
                'descuento' => $descuento,
                'tipo' => $tipoDescuento,
                'valor' => $valorDescuento,
                'moneda_especifica' => $cupon->moneda ? $cupon->moneda->codigo_iso : null
            ],
            'totales' => [
                'subtotal' => $subtotalActual,
                'subtotal_formateado' => $monedaActual->simbolo . ' ' . number_format($subtotalActual, 2),
                'descuento' => $descuento,
                'descuento_formateado' => $monedaActual->simbolo . ' ' . number_format($descuento, 2),
                'total' => $totalConDescuento,
                'total_formateado' => $monedaActual->simbolo . ' ' . number_format($totalConDescuento, 2)
            ]
        ]);
    }

    // Quitar cupón
    public function quitarCupon()
    {
        session()->forget('cupon_aplicado');

        return response()->json([
            'success' => true,
            'message' => 'Cupón eliminado'
        ]);
    }

    private function obtenerMonedaActual()
    {
        if (session()->has('moneda_seleccionada')) {
            $moneda = Moneda::where('codigo_iso', session('moneda_seleccionada'))
                ->where('is_active', true)
                ->first();
            if ($moneda) return $moneda;
        }

        if (Auth::guard('client')->check() && Auth::guard('client')->user()->client) {
            $cliente = Auth::guard('client')->user()->client;
            if ($cliente && $cliente->pais) {
                $moneda = Moneda::where('pais', $cliente->pais)
                    ->where('is_active', true)
                    ->first();
                if ($moneda) return $moneda;
            }
        }

        try {
            $geoInfo = GeoLocation::getCountryInfo();
            if (isset($geoInfo['country'])) {
                $moneda = Moneda::where('pais', $geoInfo['country'])
                    ->where('is_active', true)
                    ->first();
                if ($moneda) return $moneda;
            }
        } catch (\Exception $e) {
            Log::warning('Error en geolocalización: ' . $e->getMessage());
        }

        $moneda = Moneda::where('codigo_iso', 'USD')
            ->where('is_active', true)
            ->first();

        if (!$moneda) {
            $moneda = Moneda::where('is_active', true)->first();
        }

        return $moneda;
    }

    public function procesarPago(Request $request)
    {
        try {
            // Validaciones básicas
            $request->validate([
                'metodo_pago' => 'required|in:yape,plin,paypal',
                'terminos' => 'required|accepted',
                'comentarios' => 'nullable|string|max:500'
            ]);

            // Validar archivos según método de pago
            if (in_array($request->metodo_pago, ['yape', 'plin'])) {
                $request->validate([
                    'comprobante_' . $request->metodo_pago => 'required|file|image|max:5120'
                ]);
            }

            // Obtener usuario y cliente autenticado
            $user = Auth::guard('client')->user();
            if (!$user || !$user->client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para continuar'
                ], 401);
            }

            $cliente = $user->client;
            $clienteId = $cliente->id;

            // Obtener moneda actual
            $monedaActual = $this->obtenerMonedaActual();

            // Obtener carrito activo con productos
            $carrito = Carrito::activo()
                ->with(['productos' => function($query) {
                    $query->with(['producto.stock']);
                }])
                ->where('cliente_id', $clienteId)
                ->first();

            if (!$carrito || $carrito->productos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu carrito está vacío'
                ]);
            }

            // --- VALIDAR STOCK ANTES DE PROCESAR ---
            $erroresStock = [];
            foreach ($carrito->productos as $item) {
                $producto = $item->producto;

                if ($producto->esFisico()) {
                    $stockDisponible = $producto->stock->cantidad ?? 0;

                    if ($stockDisponible < $item->cantidad) {
                        $erroresStock[] = "El producto '{$producto->nombre}' solo tiene {$stockDisponible} unidades disponibles (solicitaste {$item->cantidad})";
                    }
                }
            }

            if (!empty($erroresStock)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Problemas con el stock:',
                    'errors' => $erroresStock
                ], 400);
            }

            // Calcular totales base
            $subtotalActual = $monedaActual->codigo_iso == 'PEN'
                ? $carrito->total_local
                : $carrito->total_usd;

            $descuentoVerificado = 0;
            $cuponAplicado = null;
            $cuponId = null;

            // Verificar cupón si existe en sesión
            if (session()->has('cupon_aplicado')) {
                $cuponAplicado = session('cupon_aplicado');
                $cuponId = $cuponAplicado['id'];

                // VALIDACIÓN: Comparar monedas
                if (isset($cuponAplicado['moneda_id']) && !is_null($cuponAplicado['moneda_id'])) {
                    if ($cuponAplicado['moneda_id'] != $monedaActual->id) {
                        return response()->json([
                            'success' => false,
                            'message' => 'El cupón no es válido para la moneda actual',
                            'requires_action' => 'remove_coupon_or_change_currency'
                        ], 400);
                    }
                }

                // Calcular descuento verificado
                if ($cuponAplicado['tipo'] == 'porcentaje') {
                    $descuentoVerificado = $subtotalActual * ($cuponAplicado['valor'] / 100);
                } else {
                    $descuentoVerificado = $cuponAplicado['valor'];
                    $descuentoVerificado = min($descuentoVerificado, $subtotalActual);
                }
            }

            // Calcular total final
            $totalFinal = $subtotalActual - $descuentoVerificado;

            // --- 1. CREAR SOLICITUD DE PAGO ---
            $solicitudData = [
                'cliente_id' => $clienteId,
                'carrito_id' => $carrito->id,
                'moneda_id' => $monedaActual->id,
                'monto' => $totalFinal,
                'metodo_pago' => $request->metodo_pago
            ];

            // Agregar cupon_id solo si existe
            if ($cuponId) {
                $solicitudData['cupon_id'] = $cuponId;
            }

            // Procesar y guardar imagen del comprobante
            if ($request->metodo_pago == 'yape' && $request->hasFile('comprobante_yape')) {
                $path = $request->file('comprobante_yape')->store('comprobantes/yape', 'public');
                $solicitudData['imagen_1'] = $path;
            }

            if ($request->metodo_pago == 'plin' && $request->hasFile('comprobante_plin')) {
                $path = $request->file('comprobante_plin')->store('comprobantes/plin', 'public');
                $solicitudData['imagen_1'] = $path;
            }

            // Crear la solicitud
            $solicitud = SolicitudPago::create($solicitudData);

            // --- 2. REGISTRAR ESTADO INICIAL DE LA SOLICITUD ---
            SolicitudPagoEstado::create([
                'solicitud_pago_id' => $solicitud->id,
                'estado' => 'solicitado',
                'comentarios' => $request->comentarios ?? 'Solicitud de pago creada por el cliente'
            ]);

            // --- 3. ACTUALIZAR STOCK DE PRODUCTOS FÍSICOS ---
            foreach ($carrito->productos as $item) {
                $producto = $item->producto;

                if ($producto->esFisico() && $producto->stock) {
                    $producto->stock->reducirStock($item->cantidad);

                    Log::info('Stock actualizado', [
                        'producto' => $producto->nombre,
                        'cantidad_comprada' => $item->cantidad,
                        'stock_restante' => $producto->stock->cantidad,
                        'solicitud_id' => $solicitud->id
                    ]);
                }
            }

            // --- 4. ACTUALIZAR ESTADO DEL CARRITO ---
            $carrito->update(['estado' => 'procesando']);

            // --- 5. REGISTRAR USO DEL CUPÓN SI EXISTE ---
            if ($cuponAplicado && $descuentoVerificado > 0) {
                CuponUsado::create([
                    'cupon_id' => $cuponAplicado['id'],
                    'cliente_id' => $clienteId,
                    'carrito_id' => $carrito->id,
                    'moneda_id' => $monedaActual->id,
                    'descuento_obtenido_monto' => $descuentoVerificado,
                    'descuento_obtenido_porcentaje' => $cuponAplicado['tipo'] == 'porcentaje' ? $cuponAplicado['valor'] : null
                ]);

                // Actualizar stock del cupón
                $cupon = Cupon::find($cuponAplicado['id']);
                if ($cupon) {
                    $cupon->stok_actual -= 1;
                    $cupon->save();

                    Log::info('Stock de cupón actualizado', [
                        'cupon' => $cupon->codigo,
                        'stock_restante' => $cupon->stok_actual,
                        'solicitud_id' => $solicitud->id
                    ]);
                }

                // Limpiar cupón de sesión
                session()->forget('cupon_aplicado');
            }

            // Generar número de solicitud
            $numeroSolicitud = 'SOL-' . date('Ymd') . '-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de pago recibida correctamente',
                'numero_solicitud' => $numeroSolicitud,
                'solicitud_id' => $solicitud->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // CORREGIDO: No usar $request->all() cuando hay archivos
            Log::error('Error en procesarPago: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'solicitud_id' => $solicitud->id ?? null,
                'metodo_pago' => $request->metodo_pago ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago. Por favor intenta nuevamente.'
            ], 500);
        }
    }
}
