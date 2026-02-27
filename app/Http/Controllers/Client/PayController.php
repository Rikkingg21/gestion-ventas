<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
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

        // ============================================
        // MISMA LÓGICA DE MONEDA QUE EN PRODUCTOS
        // ============================================
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

        // Calcular totales en la moneda actual
        $totalActual = $monedaActual->codigo_iso == 'PEN'
            ? $carrito->total_local
            : $carrito->total_usd;

        $totales = (object)[
            'subtotal_usd' => $carrito->total_usd,
            'subtotal_local' => $carrito->total_local,
            'subtotal_actual' => $totalActual,
            'subtotal_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($totalActual, 2),
            'total_usd' => $carrito->total_usd,
            'total_local' => $carrito->total_local,
            'total_actual' => $totalActual,
            'total_actual_formateado' => $monedaActual->simbolo . ' ' . number_format($totalActual, 2),
            'total_items' => $carrito->total_items,
            'moneda_actual' => $monedaActual
        ];

        // Obtener todas las monedas activas para el selector (opcional)
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

    /**
     * Obtener imagen principal del producto
     */
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

    /**
     * Procesar el pago (ejemplo)
     */
    public function procesarPago(Request $request)
    {
        // Aquí iría la lógica para procesar el pago
        // Recibirías datos de la tarjeta, método de pago, etc.

        try {
            DB::beginTransaction();

            $user = Auth::guard('client')->user();
            $cliente = $user->client;

            // Obtener moneda actual (misma lógica que en index)
            $monedaActual = $this->getMonedaActual($cliente);

            $carrito = Carrito::activo()
                ->where('cliente_id', $cliente->id)
                ->first();

            if (!$carrito || $carrito->productos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carrito vacío'
                ], 400);
            }

            // Crear solicitud de pago
            $solicitud = SolicitudPago::create([
                'cliente_id' => $cliente->id,
                'monto' => $monedaActual->codigo_iso == 'PEN'
                    ? $carrito->total_local
                    : $carrito->total_usd,
                'moneda' => $monedaActual->codigo_iso,
                'estado' => 'pendiente',
                // otros campos...
            ]);

            // Crear registro de estado
            SolicitudPagoEstado::create([
                'solicitud_pago_id' => $solicitud->id,
                'estado' => 'pendiente',
                'observaciones' => 'Pago iniciado'
            ]);

            // Aquí iría la integración con pasarela de pago

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago procesado correctamente',
                'redirect' => route('checkout.confirmacion', $solicitud->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar pago: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago'
            ], 500);
        }
    }

    /**
     * Helper para obtener moneda actual
     */
    private function getMonedaActual($cliente)
    {
        // 1. Moneda en sesión
        if (session()->has('moneda_seleccionada')) {
            $moneda = Moneda::where('codigo_iso', session('moneda_seleccionada'))
                ->where('is_active', true)
                ->first();
            if ($moneda) return $moneda;
        }

        // 2. País del cliente
        if ($cliente && $cliente->pais) {
            $moneda = Moneda::where('pais', $cliente->pais)
                ->where('is_active', true)
                ->first();
            if ($moneda) return $moneda;
        }

        // 3. Geolocalización
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

        // 4. USD por defecto
        $moneda = Moneda::where('codigo_iso', 'USD')->first();
        if ($moneda) return $moneda;

        // 5. Fallback extremo
        return (object)[
            'id' => 0,
            'codigo_iso' => 'USD',
            'simbolo' => '$',
            'nombre' => 'Dólar Americano'
        ];
    }
    public function store(Request $request)
    {
        $request->validate([
            'metodo_pago' => 'required|in:yape,plin,paypal',
            'comprobante_yape' => 'required_if:metodo_pago,yape|image|max:5120',
            'comprobante_plin' => 'required_if:metodo_pago,plin|image|max:5120',
            'comentarios' => 'nullable|string|max:500',
            'terminos' => 'accepted'
        ], [
            'metodo_pago.required' => 'Debes seleccionar un método de pago',
            'comprobante_yape.required_if' => 'Debes adjuntar el comprobante de Yape',
            'comprobante_plin.required_if' => 'Debes adjuntar el comprobante de Plin',
            'comprobante_yape.image' => 'El archivo debe ser una imagen',
            'comprobante_plin.image' => 'El archivo debe ser una imagen',
            'comprobante_yape.max' => 'La imagen no debe pesar más de 5MB',
            'comprobante_plin.max' => 'La imagen no debe pesar más de 5MB',
            'terminos.accepted' => 'Debes aceptar los términos y condiciones'
        ]);

        // Obtener el usuario autenticado
        $user = Auth::guard('client')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión'
            ], 401);
        }

        // Obtener el cliente asociado al usuario
        $cliente = $user->client;

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un perfil de cliente válido'
            ], 403);
        }

        // Buscar el carrito activo del cliente
        $carrito = Carrito::activo()
            ->with(['productos.producto'])
            ->where('cliente_id', $cliente->id)
            ->first();

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un carrito activo'
            ], 404);
        }

        if ($carrito->productos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu carrito está vacío'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Subir imágenes si existen
            $imagenes = [];

            if ($request->hasFile('comprobante_yape')) {
                $path = $request->file('comprobante_yape')->store('solicitudes/yape', 'public');
                $imagenes[0] = $path;
            }

            if ($request->hasFile('comprobante_plin')) {
                $path = $request->file('comprobante_plin')->store('solicitudes/plin', 'public');
                $imagenes[1] = $path;
            }

            // Crear la solicitud de pago
            $solicitud = SolicitudPago::create([
                'cliente_id' => $cliente->id,
                'carrito_id' => $carrito->id,
                'monto' => $carrito->total_local,
                'metodo_pago' => $request->metodo_pago,
                'imagen_1' => $imagenes[0] ?? null,
                'imagen_2' => $imagenes[1] ?? null,
                'imagen_3' => $imagenes[2] ?? null,
                'estado' => 'pendiente'
            ]);

            // Crear el primer estado en el historial
            SolicitudPagoEstado::create([
                'solicitud_pago_id' => $solicitud->id,
                'estado' => 'pendiente',
                'comentarios' => $request->comentarios ?? 'Solicitud de pago creada'
            ]);

            // Marcar el carrito como procesado (cambiar estado)
            $carrito->update(['estado' => 'procesado']);

            DB::commit();

            // Limpiar la sesión del carrito
            session()->forget('carrito_id');

            // Si es PayPal, podrías devolver una URL de redirección
            $redirectUrl = $request->metodo_pago === 'paypal'
                ? route('paypal.redirect', ['solicitud' => $solicitud->id])
                : route('cliente.mis-compras');

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de pago creada exitosamente',
                'solicitud_id' => $solicitud->id,
                'redirect' => $redirectUrl,
                'metodo_pago' => $request->metodo_pago
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar el error para debugging
            Log::error('Error en solicitud de pago: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud. Por favor, intenta nuevamente.'
            ], 500);
        }
    }
    public function misCompras()
    {
        $cliente = Auth::guard('client')->user();

        $solicitudes = SolicitudPago::with(['carrito', 'boleta'])
            ->where('cliente_id', $cliente->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.compras.index', compact('solicitudes'));
    }

    public function show($id)
    {
        $solicitud = SolicitudPago::with(['carrito.productos.producto', 'boleta', 'estados'])
            ->where('id', $id)
            ->where('cliente_id', Auth::guard('client')->id())
            ->firstOrFail();

        return view('client.compras.show', compact('solicitud'));
    }
}
