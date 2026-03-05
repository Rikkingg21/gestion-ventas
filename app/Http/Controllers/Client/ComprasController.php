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
use App\Helpers\GeoLocation;

class ComprasController extends Controller
{
    public function index()
    {
        $user = Auth::guard('client')->user();

        if (!$user || !$user->client) {
            return redirect()->route('client.login')
                ->with('error', 'Debes iniciar sesión para ver tus compras');
        }

        $cliente = $user->client;
        $clienteId = $cliente->id;

        // Obtener las solicitudes del cliente con sus estados
        $solicitudes = SolicitudPago::where('cliente_id', $clienteId)
            ->with(['estados' => function($query) {
                $query->orderBy('created_at', 'desc');
            }, 'carrito'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.compras.index', compact('solicitudes', 'cliente'));
    }

    public function show($id)
    {
        $user = Auth::guard('client')->user();
        $cliente = $user->client;

        // Obtener una solicitud específica con todas sus relaciones
        $solicitud = SolicitudPago::where('cliente_id', $cliente->id)
            ->with([
                'estados' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'carrito.productos.producto',
                'carrito.productos.producto.categoria'
            ])
            ->findOrFail($id);

        return view('client.compras.show', compact('solicitud', 'cliente'));
    }
}
