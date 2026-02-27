<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\SolicitudPago;
use App\Models\SolicitudPagoEstado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PayController extends Controller
{
    public function index()
    {
        // Obtener el usuario autenticado (con cualquier guard, todos usan User)
        $user = Auth::guard('client')->user();

        if (!$user) {
            return redirect()->route('client.login')
                ->with('error', 'Debes iniciar sesión para continuar')
                ->with('redirect_to', route('checkout.index'));
        }

        // Obtener el cliente asociado al usuario
        $cliente = $user->client; // Asumiendo que tienes esta relación en User

        if (!$cliente) {
            return redirect()->route('client.login')
                ->with('error', 'No tienes un perfil de cliente asociado');
        }

        $carrito = Carrito::activo()
            ->with(['productos.producto'])
            ->where('cliente_id', $cliente->id)
            ->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return redirect()->route('carrito.ver')
                ->with('error', 'Tu carrito está vacío');
        }

        $items = $carrito->productos;
        $totales = (object)[
            'subtotal_local' => $carrito->total_local,
            'total_local' => $carrito->total_local,
            'total_usd' => $carrito->total_usd,
            'total_items' => $carrito->total_items
        ];

        return view('client.checkout.index', compact('items', 'totales', 'carrito', 'user', 'cliente'));
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
