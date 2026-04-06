<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\MetodoPagoHelper;
use App\Helpers\MonedaHelper;
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
use Illuminate\Support\Str;
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

        // USAR LA MISMA LÓGICA DE MONEDA QUE EL CARRITO
        $monedaActual = MonedaHelper::getMonedaActual();
        $aceptaPagos = $monedaActual->aceptar_pagos ?? false;

        // Obtener la moneda que se usará para cobrar (USD si no acepta pagos)
        $monedaACobrar = $aceptaPagos ? $monedaActual : Moneda::where('codigo_iso', 'USD')->first();

        // Obtener carrito activo del cliente
        $carrito = Carrito::activo()
            ->with(['productos' => function($query) {
                $query->with(['producto' => function($q) {
                    $q->with(['categoria', 'precios.moneda', 'stock']);
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

        // Procesar items usando la misma lógica del carrito
        $itemsProcesados = $this->procesarItemsCheckout($carrito, $monedaActual, $aceptaPagos);

        // Calcular totales usando la misma lógica del carrito
        $totales = $this->calcularTotalesCheckout($carrito, $monedaActual, $aceptaPagos, $monedaACobrar, $cuponAplicado);

        // Obtener país para filtrar métodos de pago
        $paisCode = $cliente->pais ?? null;

        // OBTENER MÉTODOS DE PAGO FILTRADOS POR MONEDA ACTUAL
        $metodosPagoData = MetodoPagoHelper::getMetodosPagoData($monedaActual, $paisCode);

        return view('client.checkout.index', compact(
            'itemsProcesados',
            'totales',
            'carrito',
            'user',
            'cliente',
            'monedaActual',
            'aceptaPagos',
            'monedaACobrar',
            'metodosPagoData'
        ));
    }

    private function procesarItemsCheckout($carrito, $monedaActual, $aceptaPagos)
    {
        return $carrito->productos->map(function($item) use ($carrito, $monedaActual, $aceptaPagos) {
            $producto = $item->producto;

            // Calcular el precio a mostrar en la moneda actual
            if ($aceptaPagos) {
                // Si la moneda actual acepta pagos, mostrar el precio en la moneda local
                $precioMostrar = $item->precio_adquirido_local;
                $subtotalMostrar = $item->subtotal_local;
            } else {
                // Si NO acepta pagos, mostrar el precio_local (ya está en la moneda actual)
                $precioMostrar = $item->precio_adquirido_local;
                $subtotalMostrar = $item->subtotal_local;
            }

            return (object)[
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $producto ? $producto->nombre : 'Producto no disponible',
                'cantidad' => $item->cantidad,
                'precio_unitario_usd' => $item->precio_adquirido_usd,
                'precio_unitario_local' => $item->precio_adquirido_local,
                'precio_mostrar' => $precioMostrar,
                'subtotal_mostrar' => $subtotalMostrar,
                'imagen' => $this->getImagenProducto($producto),
                'tipo_producto' => $producto ? $producto->tipo_producto : null,
                'es_fisico' => $producto ? $producto->esFisico() : false,
                'es_digital' => $producto ? $producto->esDigital() : false,
                'aplica_descuento' => $item->aplica_descuento,
                'porcentaje_descuento' => $item->porcentaje_descuento,
            ];
        });
    }

    private function calcularTotalesCheckout($carrito, $monedaActual, $aceptaPagos, $monedaACobrar, $cuponAplicado = null)
    {
        // Calcular total actual para mostrar (siempre en la moneda actual para visualización)
        if ($aceptaPagos) {
            $totalActual = $carrito->moneda_id == $monedaActual->id
                ? $carrito->total_local
                : $carrito->total_usd;
        } else {
            $totalActual = $carrito->total_local;
        }

        $subtotalActual = $totalActual;
        $descuentoAplicado = 0;
        $totalConDescuento = $totalActual;

        // Aplicar descuento del cupón si existe
        if ($cuponAplicado) {
            if ($cuponAplicado['tipo'] == 'porcentaje') {
                $descuentoAplicado = $subtotalActual * ($cuponAplicado['valor'] / 100);
            } else {
                $descuentoAplicado = $cuponAplicado['valor'];
                $descuentoAplicado = min($descuentoAplicado, $subtotalActual);
            }
            $totalConDescuento = $subtotalActual - $descuentoAplicado;
        }

        return (object)[
            'total_usd' => $carrito->total_usd,
            'total_local' => $carrito->total_local,
            'total_actual' => $totalConDescuento,
            'total_items' => $carrito->total_items,
            'subtotal_actual' => $subtotalActual,
            'subtotal_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($subtotalActual, 2),
            'descuento_aplicado' => $descuentoAplicado,
            'descuento_aplicado_formateado' => $monedaActual->simbolo . ' ' . number_format($descuentoAplicado, 2),
            'total_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($totalConDescuento, 2),
            'moneda_actual' => (object)[
                'id' => $monedaActual->id,
                'codigo_iso' => $monedaActual->codigo_iso,
                'simbolo' => $monedaActual->simbolo,
                'nombre' => $monedaActual->nombre,
                'tasa_cambio_usd' => $monedaActual->tasa_cambio_usd ?? 1,
                'acepta_pagos' => $aceptaPagos
            ],
            'acepta_pagos' => $aceptaPagos,
            'moneda_a_cobrar' => $aceptaPagos ? null : (object)[
                'id' => $monedaACobrar->id,
                'codigo_iso' => $monedaACobrar->codigo_iso,
                'simbolo' => $monedaACobrar->simbolo,
                'nombre' => $monedaACobrar->nombre,
            ],
            'cupon_aplicado' => $cuponAplicado
        ];
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

        // Obtener moneda actual usando MonedaHelper
        $monedaActual = MonedaHelper::getMonedaActual();
        $aceptaPagos = $monedaActual->aceptar_pagos ?? false;

        // Calcular subtotal actual (en la moneda actual para mostrar)
        $subtotalActual = $aceptaPagos
            ? ($carrito->moneda_id == $monedaActual->id ? $carrito->total_local : $carrito->total_usd)
            : $carrito->total_local;

        // Validar que el cupón sea aplicable a la moneda actual
        if ($cupon->moneda_id && $cupon->moneda_id != $monedaActual->id) {
            $monedaCupon = Moneda::find($cupon->moneda_id);
            return response()->json([
                'success' => false,
                'message' => 'Este cupón solo aplica para compras en ' . ($monedaCupon ? $monedaCupon->nombre : 'otra moneda')
            ]);
        }

        // Calcular descuento según el tipo de cupón
        $descuento = 0;
        $tipoDescuento = null;
        $valorDescuento = 0;

        if (!is_null($cupon->porcentaje_descuento) && $cupon->porcentaje_descuento > 0) {
            $descuento = $subtotalActual * ($cupon->porcentaje_descuento / 100);
            $tipoDescuento = 'porcentaje';
            $valorDescuento = $cupon->porcentaje_descuento;
        } elseif (!is_null($cupon->descuento_monto) && $cupon->descuento_monto > 0) {
            $descuento = $cupon->descuento_monto;
            $tipoDescuento = 'monto_fijo';
            $valorDescuento = $cupon->descuento_monto;
            $descuento = min($descuento, $subtotalActual);
        } else {
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

        $totalConDescuento = $subtotalActual - $descuento;

        $mensajeDescuento = $tipoDescuento == 'porcentaje'
            ? "{$valorDescuento}% de descuento"
            : "{$monedaActual->simbolo} " . number_format($valorDescuento, 2) . " de descuento";

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

    public function procesarPago(Request $request)
    {
        try {
            // 1. Verificar términos y condiciones
            if (!$request->terminos || $request->terminos != '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes aceptar los términos y condiciones'
                ], 422);
            }

            // 2. Verificar método de pago
            if (!$request->metodo_pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes seleccionar un método de pago'
                ], 422);
            }

            // 3. Obtener método de pago de la BD
            $metodoPago = MetodoPagoHelper::getMetodoBySlug($request->metodo_pago);

            if (!$metodoPago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Método de pago no válido'
                ], 400);
            }

            // 4. Validar campos requeridos según el método
            $infoPago = [];

            if ($metodoPago->tipo == 'billetera_digital') {
                if (!$request->numero_referencia) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes ingresar el número de operación/referencia'
                    ], 422);
                }
                $infoPago['numero_referencia'] = $request->numero_referencia;
            }

            if ($metodoPago->tipo == 'cuenta_bancaria') {
                if (!$request->numero_operacion) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes ingresar el número de operación'
                    ], 422);
                }
                $infoPago['numero_operacion'] = $request->numero_operacion;

                if (!$request->banco_origen) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes ingresar el banco de origen'
                    ], 422);
                }
                $infoPago['banco_origen'] = $request->banco_origen;
            }

            if ($metodoPago->tipo == 'transferencia_email') {
                if (!$request->email_paypal) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes ingresar tu email de PayPal'
                    ], 422);
                }
                $infoPago['email_paypal'] = $request->email_paypal;

                if (!$request->id_transaccion) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes ingresar el ID de transacción'
                    ], 422);
                }
                $infoPago['id_transaccion'] = $request->id_transaccion;
            }

            // 5. Guardar imágenes
            $imagenes = ['imagen_1' => null, 'imagen_2' => null, 'imagen_3' => null];

            if ($request->hasFile('comprobante_1')) {
                $file = $request->file('comprobante_1');
                $extension = $file->getClientOriginalExtension();
                $nombreUnico = Str::random(40) . '.' . $extension;
                $path = $file->storeAs('comprobantes/' . $metodoPago->slug, $nombreUnico, 'public');
                $imagenes['imagen_1'] = $path;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes adjuntar el comprobante de pago'
                ], 422);
            }

            if ($request->hasFile('comprobante_2')) {
                $file = $request->file('comprobante_2');
                $extension = $file->getClientOriginalExtension();
                $nombreUnico = Str::random(40) . '.' . $extension;
                $path = $file->storeAs('comprobantes/' . $metodoPago->slug, $nombreUnico, 'public');
                $imagenes['imagen_2'] = $path;
            }

            if ($request->hasFile('comprobante_3')) {
                $file = $request->file('comprobante_3');
                $extension = $file->getClientOriginalExtension();
                $nombreUnico = Str::random(40) . '.' . $extension;
                $path = $file->storeAs('comprobantes/' . $metodoPago->slug, $nombreUnico, 'public');
                $imagenes['imagen_3'] = $path;
            }

            // 6. Agregar comentarios
            if ($request->comentarios) {
                $infoPago['comentarios_cliente'] = $request->comentarios;
            }

            // 7. Obtener usuario y carrito
            $user = Auth::guard('client')->user();
            if (!$user || !$user->client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para continuar'
                ], 401);
            }

            $cliente = $user->client;
            $clienteId = $cliente->id;

            $carrito = Carrito::activo()
                ->with(['productos.producto'])
                ->where('cliente_id', $clienteId)
                ->first();

            if (!$carrito || $carrito->productos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu carrito está vacío'
                ], 400);
            }

            // 8. Obtener moneda actual usando MonedaHelper
            $monedaActual = MonedaHelper::getMonedaActual();
            $aceptaPagos = $monedaActual->aceptar_pagos ?? false;

            // 9. Determinar la moneda para el cobro y el monto real
            // Si la moneda actual NO acepta pagos, se cobra en USD
            $monedaCobro = $aceptaPagos ? $monedaActual : Moneda::where('codigo_iso', 'USD')->first();

            // Calcular el monto en la moneda de cobro
            if ($aceptaPagos) {
                // Si acepta pagos, usar el monto en la moneda actual
                $montoCobro = $carrito->moneda_id == $monedaActual->id
                    ? $carrito->total_local
                    : $carrito->total_usd;
            } else {
                // Si NO acepta pagos, usar el monto en USD (moneda base)
                $montoCobro = $carrito->total_usd;
            }

            // Aplicar descuento del cupón si existe
            $descuentoVerificado = 0;
            $cuponId = null;

            if (session()->has('cupon_aplicado')) {
                $cuponAplicado = session('cupon_aplicado');
                $cuponId = $cuponAplicado['id'];

                if ($cuponAplicado['tipo'] == 'porcentaje') {
                    $descuentoVerificado = $montoCobro * ($cuponAplicado['valor'] / 100);
                } else {
                    $descuentoVerificado = min($cuponAplicado['valor'], $montoCobro);
                }

                $infoPago['cupon_aplicado'] = [
                    'codigo' => $cuponAplicado['codigo'],
                    'tipo' => $cuponAplicado['tipo'],
                    'valor' => $cuponAplicado['valor'],
                    'descuento' => $descuentoVerificado,
                    'moneda' => $monedaCobro->simbolo,
                    'moneda_codigo' => $monedaCobro->codigo_iso
                ];
            }

            $totalFinal = $montoCobro - $descuentoVerificado;

            // Guardar información de la moneda de cobro en info_pago para referencia
            $infoPago['moneda_cobro'] = [
                'id' => $monedaCobro->id,
                'codigo' => $monedaCobro->codigo_iso,
                'simbolo' => $monedaCobro->simbolo,
                'nombre' => $monedaCobro->nombre
            ];

            if (!$aceptaPagos) {
                $infoPago['moneda_visualizacion'] = [
                    'id' => $monedaActual->id,
                    'codigo' => $monedaActual->codigo_iso,
                    'simbolo' => $monedaActual->simbolo,
                    'nombre' => $monedaActual->nombre
                ];
            }

            // 10. CREAR SOLICITUD DE PAGO
            $solicitudData = [
                'cliente_id' => $clienteId,
                'carrito_id' => $carrito->id,
                'moneda_id' => $monedaCobro->id, // Usar la moneda de cobro, no la de visualización
                'monto' => $totalFinal,
                'metodo_pago_id' => $metodoPago->id,
                'info_pago' => json_encode($infoPago, JSON_UNESCAPED_UNICODE),
                'imagen_1' => $imagenes['imagen_1'],
                'imagen_2' => $imagenes['imagen_2'],
                'imagen_3' => $imagenes['imagen_3']
            ];

            if ($cuponId) {
                $solicitudData['cupon_id'] = $cuponId;
            }

            $solicitud = SolicitudPago::create($solicitudData);

            // 11. REGISTRAR ESTADO INICIAL
            SolicitudPagoEstado::create([
                'solicitud_pago_id' => $solicitud->id,
                'estado' => 'solicitado',
                'comentarios' => null
            ]);

            // 12. ACTUALIZAR STOCK DE PRODUCTOS FÍSICOS
            foreach ($carrito->productos as $item) {
                $producto = $item->producto;
                if ($producto->esFisico() && $producto->stock) {
                    $producto->stock->reducirStock($item->cantidad);
                }
            }

            // 13. ACTUALIZAR ESTADO DEL CARRITO
            $carrito->update(['estado' => 'procesando']);

            // 14. REGISTRAR USO DEL CUPÓN
            if ($descuentoVerificado > 0 && session()->has('cupon_aplicado')) {
                $cuponAplicado = session('cupon_aplicado');
                CuponUsado::create([
                    'cupon_id' => $cuponAplicado['id'],
                    'cliente_id' => $clienteId,
                    'carrito_id' => $carrito->id,
                    'moneda_id' => $monedaCobro->id, // Usar la moneda de cobro
                    'descuento_obtenido_monto' => $descuentoVerificado,
                    'descuento_obtenido_porcentaje' => $cuponAplicado['tipo'] == 'porcentaje' ? $cuponAplicado['valor'] : null
                ]);

                $cupon = Cupon::find($cuponAplicado['id']);
                if ($cupon) {
                    $cupon->stok_actual -= 1;
                    $cupon->save();
                }

                session()->forget('cupon_aplicado');
            }

            $numeroSolicitud = 'SOL-' . date('Ymd') . '-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT);

            $mensajeExito = 'Solicitud de pago recibida correctamente. ';
            if ($metodoPago->tipo === 'transferencia_email') {
                $mensajeExito .= 'Serás redirigido para completar el pago.';
            } else {
                $mensajeExito .= 'Revisaremos tu comprobante y te contactaremos pronto.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensajeExito,
                'numero_solicitud' => $numeroSolicitud,
                'solicitud_id' => $solicitud->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error en procesarPago: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago. Por favor intenta nuevamente.'
            ], 500);
        }
    }
}
