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
use Illuminate\Support\Str;
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

        // Obtener las solicitudes del cliente con sus relaciones
        $solicitudes = SolicitudPago::where('cliente_id', $clienteId)
            ->with([
                'estados' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'carrito',
                'metodoPago',  // Agregar relación con método de pago
                'moneda'       // Agregar relación con moneda
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.compras.index', compact('solicitudes', 'cliente'));
    }

    public function show($id)
    {
        $user = Auth::guard('client')->user();
        $cliente = $user->client;

        $solicitud = SolicitudPago::where('cliente_id', $cliente->id)
            ->with([
                'estados' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'carrito.productos.producto',
                'carrito.productos.producto.categoria',
                'metodoPago',
                'moneda',
                'cupon'
            ])
            ->findOrFail($id);

        // Decodificar info_pago si es necesario y asegurar que sea un array
        if (is_string($solicitud->info_pago)) {
            $decoded = json_decode($solicitud->info_pago, true);
            $solicitud->info_pago = is_array($decoded) ? $decoded : [];
        } elseif (!is_array($solicitud->info_pago)) {
            $solicitud->info_pago = [];
        }

        return view('client.compras.show', compact('solicitud', 'cliente'));
    }
    public function verComprobante($hash)
    {
        $user = Auth::guard('client')->user();

        if (!$user || !$user->client) {
            abort(403, 'No autorizado');
        }

        $cliente = $user->client;
        $clienteId = $cliente->id;

        // Buscar la solicitud que pertenezca a este cliente y que contenga este archivo
        $solicitud = SolicitudPago::where('cliente_id', $clienteId)
            ->where(function($query) use ($hash) {
                $query->where('imagen_1', 'LIKE', "%{$hash}%")
                    ->orWhere('imagen_2', 'LIKE', "%{$hash}%")
                    ->orWhere('imagen_3', 'LIKE', "%{$hash}%");
            })
            ->first();

        if (!$solicitud) {
            abort(404, 'Comprobante no encontrado o no tienes permisos para verlo');
        }

        // Determinar qué imagen es
        $imagenNumero = null;
        $rutaImagen = null;

        for ($i = 1; $i <= 3; $i++) {
            $campo = "imagen_{$i}";
            if (isset($solicitud->$campo) && str_contains($solicitud->$campo ?? '', $hash)) {
                $imagenNumero = $i;
                $rutaImagen = $solicitud->$campo;
                break;
            }
        }

        if (!$imagenNumero || !$rutaImagen) {
            abort(404, 'Comprobante no encontrado');
        }

        // Construir la ruta completa del archivo
        $path = storage_path('app/public/' . $rutaImagen);

        if (!file_exists($path)) {
            abort(404, 'El archivo no existe');
        }

        // Determinar el tipo de contenido
        $mimeType = mime_content_type($path);

        // Devolver la imagen para visualización
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="comprobante_' . $solicitud->id . '_' . $imagenNumero . '.png"',
            'Cache-Control' => 'private, max-age=86400'
        ]);
    }
    public function descargarComprobante($hash)
    {
        $user = Auth::guard('client')->user();

        if (!$user || !$user->client) {
            abort(403, 'No autorizado');
        }

        $cliente = $user->client;

        $solicitud = SolicitudPago::where('cliente_id', $cliente->id)
            ->where(function($query) use ($hash) {
                $query->where('imagen_1', 'LIKE', "%{$hash}%")
                    ->orWhere('imagen_2', 'LIKE', "%{$hash}%")
                    ->orWhere('imagen_3', 'LIKE', "%{$hash}%");
            })
            ->first();

        if (!$solicitud) {
            abort(404, 'Comprobante no encontrado');
        }

        $rutaImagen = null;
        for ($i = 1; $i <= 3; $i++) {
            $campo = "imagen_{$i}";
            if (isset($solicitud->$campo) && str_contains($solicitud->$campo ?? '', $hash)) {
                $rutaImagen = $solicitud->$campo;
                break;
            }
        }

        if (!$rutaImagen) {
            abort(404, 'Comprobante no encontrado');
        }

        $path = storage_path('app/public/' . $rutaImagen);

        if (!file_exists($path)) {
            abort(404, 'El archivo no existe');
        }

        return response()->download($path, 'comprobante_' . $solicitud->id . '.png');
    }
    public function verArchivoDrive($id_compras, $id_producto)
    {
        $user = Auth::guard('client')->user();

        if (!$user || !$user->client) {
            abort(403, 'No autorizado');
        }

        $clienteId = $user->client->id;

        // 1. Verificar que la compra (solicitud) pertenezca al cliente
        $solicitud = SolicitudPago::where('id', $id_compras)
            ->where('cliente_id', $clienteId)
            ->with(['carrito.productos.producto']) // Carga los productos del carrito
            ->firstOrFail();

        // 2. Buscar el producto específico dentro del carrito de esa compra
        $productoEncontrado = null;
        foreach ($solicitud->carrito->productos as $item) {
            if ($item->producto_id == $id_producto) {
                $productoEncontrado = $item->producto;
                break;
            }
        }

        if (!$productoEncontrado) {
            abort(404, 'Producto no encontrado en esta compra.');
        }

        // 3. Verificar que sea un producto digital
        if (!$productoEncontrado->esDigital()) {
            abort(404, 'Este producto no es digital o no tiene contenido asociado.');
        }

        // 4. Obtener la URL de vista previa para Google Drive
        $driveEmbedUrl = $this->getDriveEmbedUrl($productoEncontrado->url_recurso);

        // 5. Retornar la vista con los datos necesarios
        return view('client.compras.drive', [
            'solicitud' => $solicitud,
            'producto' => $productoEncontrado,
            'driveEmbedUrl' => $driveEmbedUrl,
        ]);
    }

    private function getDriveEmbedUrl($url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $driveId = null;

        // --- Patrón para CARPETA (formato que me compartiste) ---
        // Ejemplo: https://drive.google.com/drive/folders/ID_DEL_REPO?usp=drive_link
        if (preg_match('/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $driveId = $matches[1];
            // URL de embedding para carpeta
            return "https://drive.google.com/embeddedfolderview?id={$driveId}#list";
        }

        // --- Patrón para ARCHIVO (si lo necesitas a futuro) ---
        // Ejemplo: https://drive.google.com/file/d/ID_DEL_ARCHIVO/view
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $driveId = $matches[1];
            // URL de vista previa para archivo
            return "https://drive.google.com/file/d/{$driveId}/preview";
        }

        // --- Patrón alternativo con 'open?id=' ---
        if (preg_match('/open\?id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $driveId = $matches[1];
            return "https://drive.google.com/file/d/{$driveId}/preview";
        }

        // --- Si solo recibimos el ID (útil para pruebas) ---
        if (preg_match('/^[a-zA-Z0-9_-]{25,}$/', $url)) {
            // Asumimos que es un ID de carpeta
            return "https://drive.google.com/embeddedfolderview?id={$url}#list";
        }

        // Si no se pudo procesar, devolvemos null
        return null;
    }
}
