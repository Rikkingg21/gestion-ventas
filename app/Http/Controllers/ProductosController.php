<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Carrito;
use App\Models\CarritoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ProductosController extends Controller
{
    public function index()
    {
        // Obtener categorías activas
        $categorias = Categoria::where('is_active', true)
            ->orderBy('nombre')
            ->get();

        // Obtener productos activos con sus relaciones
        $productos = Producto::active()
            ->with(['categoria', 'stock'])
            ->orderBy('nombre')
            ->get();

        // Obtener el carrito actual para mostrar contador
        $carrito = $this->obtenerCarritoActual();
        $totalItems = $carrito ? $carrito->total_items : 0;

        return view('client.productos.index', compact('categorias', 'productos', 'totalItems'));
    }

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
        $carrito = $this->obtenerCarritoActual();

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
                        'subtotal_local' => 0
                    ],
                    'carrito_vacio' => true
                ]);
            }

            return view('client.carrito.index', [
                'items' => collect(),
                'totales' => (object)[
                    'total_usd' => 0,
                    'total_local' => 0,
                    'total_items' => 0,
                    'subtotal_usd' => 0,
                    'subtotal_local' => 0
                ]
            ]);
        }

        // Cargar productos con sus relaciones
        $carrito->load(['productos' => function($query) {
            $query->with(['producto' => function($q) {
                $q->with('categoria');
            }]);
        }]);

        if ($request->wantsJson()) {
            // Formatear items para el frontend
            $itemsFormateados = $carrito->productos->map(function($item) {
                $producto = $item->producto;

                // Obtener imagen principal
                $imagenPrincipal = null;
                for ($i = 1; $i <= 5; $i++) {
                    $campo = 'imagen_url_' . $i;
                    if ($producto && $producto->$campo) {
                        $imagenPrincipal = $producto->getImageUrl($campo);
                        break;
                    }
                }

                return [
                    'id' => $item->id,
                    'producto_id' => $item->producto_id,
                    'nombre' => $producto ? $producto->nombre : 'Producto no disponible',
                    'cantidad' => $item->cantidad,
                    'precio_unitario_usd' => $item->precio_adquirido_usd,
                    'precio_unitario_local' => $item->precio_adquirido_local,
                    'subtotal_usd' => $item->subtotal_usd,
                    'subtotal_local' => $item->subtotal_local,
                    'imagen' => $imagenPrincipal,
                    'tipo_producto' => $producto ? $producto->tipo_producto : null,
                    'sku' => $producto ? $producto->sku : null,
                    'aplica_descuento' => $item->aplica_descuento,
                    'porcentaje_descuento' => $item->porcentaje_descuento,
                    'es_fisico' => $producto ? $producto->esFisico() : false,
                    'es_digital' => $producto ? $producto->esDigital() : false,
                    'stock_disponible' => $producto && $producto->esFisico() ? ($producto->stock->cantidad ?? 0) : null,
                    'categoria' => $producto && $producto->categoria ? $producto->categoria->nombre : null,
                    'url_recurso' => $producto && $producto->esDigital() ? $producto->url_recurso : null
                ];
            });

            return response()->json([
                'success' => true,
                'items' => $itemsFormateados,
                'totales' => [
                    'total_usd' => $carrito->total_usd,
                    'total_local' => $carrito->total_local,
                    'total_items' => $carrito->total_items,
                    'subtotal_usd' => $carrito->total_usd,
                    'subtotal_local' => $carrito->total_local
                ],
                'carrito_id' => $carrito->id,
                'carrito_vacio' => $carrito->productos->isEmpty()
            ]);
        }

        $totales = (object)[
            'total_usd' => $carrito->total_usd,
            'total_local' => $carrito->total_local,
            'total_items' => $carrito->total_items,
            'subtotal_usd' => $carrito->total_usd,
            'subtotal_local' => $carrito->total_local
        ];

        return view('client.carrito.index', [
            'items' => $carrito->productos,
            'totales' => $totales,
            'carrito_id' => $carrito->id
        ]);
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

        Log::info('No se encontró ningún carrito activo');
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
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'stock'])
            ->active()
            ->findOrFail($id);

        return view('client.productos.detalle', compact('producto'));
    }
}
