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

class ProductosController extends Controller
{
public function index(Request $request)
    {
        try {
            // Obtener categorías activas con conteo de productos
            $categorias = Cache::remember('categorias_activas_conteo', now()->addHours(6), function () {
                return Categoria::where('is_active', true)
                    ->withCount('productos')
                    ->orderBy('nombre')
                    ->get();
            });

            // Obtener moneda actual (AHORA USA EL HELPER)
            $monedaActual = MonedaHelper::getMonedaActual();

            // Obtener todos los productos activos con relaciones
            $productos = Producto::with(['categoria', 'precios.moneda', 'stock'])
                ->where('is_active', true)
                ->orderBy('nombre')
                ->paginate(12);

            // Procesar cada producto usando los helpers
            $productosProcesados = $productos->getCollection()->map(function($producto) use ($monedaActual) {
                return $this->procesarProductoParaVista($producto, $monedaActual);
            });

            $productos->setCollection($productosProcesados);

            // Total de productos
            $totalProductos = Cache::remember('total_productos_activos', now()->addHours(6), function () {
                return Producto::where('is_active', true)->count();
            });

            return view('client.productos.index', compact(
                'categorias',
                'productos',
                'totalProductos',
                'monedaActual'
            ));

        } catch (\Exception $e) {
            Log::error('Error en ProductosController@index: ' . $e->getMessage());

            return view('client.productos.index', [
                'categorias' => collect([]),
                'productos' => collect([]),
                'totalProductos' => 0,
                'monedaActual' => MonedaHelper::getMonedaActual(),
                'error' => 'Error al cargar los productos'
            ]);
        }
    }

    private function procesarProductoParaVista($producto, $monedaActual)
    {
        // ===== 1. PROCESAR IMÁGENES =====
        $imagenes = ImagenesHelper::getImagenesProducto($producto);
        $imagenesConMetadata = ImagenesHelper::getImagenesConMetadata($producto);
        $imagenPrincipal = ImagenesHelper::getImagenPrincipal($producto);
        $defaultImage = ImagenesHelper::getDefaultImage();

        // ===== 2. PROCESAR PRECIOS CON EL HELPER =====
        $precioInfo = MonedaHelper::getPrecioProductoEnMoneda($producto, $monedaActual->id ?? null);

        // ===== 3. PROCESAR STOCK =====
        $stockActual = $producto->getStockActualAttribute();
        $claseStock = $this->getClaseStock($producto, $stockActual);
        $iconoStock = $this->getIconoStock($producto, $stockActual);
        $textoStock = $this->getTextoStock($producto, $stockActual);

        // ===== 4. CONSTRUIR OBJETO FINAL =====
        return (object)[
            // Datos básicos
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'descripcion_corta' => Str::limit($producto->descripcion, 60),
            'tipo_producto' => $producto->tipo_producto,
            'categoria_id' => $producto->categoria_id,
            'categoria_nombre' => $producto->categoria->nombre ?? 'Sin categoría',
            'sku' => $producto->sku,
            'url_recurso' => $producto->url_recurso,

            // Descuentos
            'aplica_descuento' => $producto->aplica_descuento,
            'porcentaje_descuento' => $precioInfo['porcentaje_descuento'],
            'tiene_descuento' => $precioInfo['tiene_descuento'],

            // IMÁGENES
            'imagenes' => $imagenes,
            'imagenes_metadata' => $imagenesConMetadata,
            'imagen_principal' => $imagenPrincipal,
            'tiene_imagenes' => !empty($imagenes),
            'cantidad_imagenes' => count($imagenes),
            'default_image' => $defaultImage,

            // STOCK
            'stock_actual' => $stockActual,
            'es_fisico' => $producto->esFisico(),
            'es_digital' => $producto->esDigital(),
            'tiene_stock' => $producto->esFisico() ? ($stockActual > 0) : true,
            'clase_stock' => $claseStock,
            'icono_stock' => $iconoStock,
            'texto_stock' => $textoStock,

            // PRECIOS (TODO DEL HELPER)
            'moneda' => $precioInfo['moneda'],
            'precio_original' => $precioInfo['precio_original'],
            'precio_original_formateado' => $precioInfo['precio_original_formateado'],
            'precio_con_descuento' => $precioInfo['precio_con_descuento'],
            'precio_con_descuento_formateado' => $precioInfo['precio_con_descuento_formateado'],
            'ahorro' => $precioInfo['ahorro'],
            'ahorro_formateado' => $precioInfo['ahorro_formateado'],

            // Badges y clases CSS
            'badge_digital_class' => 'badge-digital',
            'badge_descuento_class' => 'discount-badge',
            'card_class' => 'product-card',
            'btn_comprar_class' => 'btn-agregar-carrito',
            'btn_ver_class' => 'btn-ver-producto'
        ];
    }
    private function getClaseStock($producto, $stockActual)
    {
        if ($producto->esDigital()) {
            return 'info';
        }

        if ($stockActual > 10) {
            return 'success';
        } elseif ($stockActual > 0) {
            return 'warning';
        }

        return 'danger';
    }
    private function getIconoStock($producto, $stockActual)
    {
        if ($producto->esDigital()) {
            return 'fa-infinity';
        }

        if ($stockActual > 0) {
            return 'fa-check-circle';
        }

        return 'fa-times-circle';
    }
    private function getTextoStock($producto, $stockActual)
    {
        if ($producto->esDigital()) {
            return 'Stock digital';
        }

        if ($stockActual > 10) {
            return $stockActual . ' disponibles';
        } elseif ($stockActual > 0) {
            return '¡Últimos ' . $stockActual . '!';
        }

        return 'Agotado';
    }

    // Obtener la moneda actual (de sesión, usuario o por defecto)
    private function getMonedaActual()
    {
        // Intentar obtener de sesión
        if (session()->has('moneda_seleccionada')) {
            $moneda = MonedaHelper::getMonedaByCodigo(session('moneda_seleccionada'));
            if ($moneda) return $moneda;
        }

        // Si no, obtener moneda por defecto
        return MonedaHelper::getMonedaDefault();
    }

    // Función privada para cargar y procesar productos
    private function cargarProductos($monedaActual)
    {
        // Obtener productos activos con todas sus relaciones
        $productos = Producto::active()
            ->with(['categoria', 'stock', 'precios.moneda'])
            ->orderBy('nombre')
            ->paginate(12); // Paginación de 12 productos

        // Procesar cada producto con sus precios
        $productos->getCollection()->transform(function($producto) use ($monedaActual) {
            // Buscar precio en moneda actual
            $precioEnMonedaActual = $producto->precios
                ->where('moneda_id', $monedaActual->id)
                ->where('is_active', true)
                ->first();

            // Si no hay precio en la moneda actual, buscar USD
            if (!$precioEnMonedaActual) {
                $precioEnMonedaActual = $producto->precios
                    ->where('moneda.codigo_iso', 'USD')
                    ->where('is_active', true)
                    ->first();
            }

            // Si aún no hay, tomar el primer precio disponible
            if (!$precioEnMonedaActual) {
                $precioEnMonedaActual = $producto->precios
                    ->where('is_active', true)
                    ->first();
            }

            // Procesar imágenes
            $imagenes = [];
            for($i = 1; $i <= 5; $i++) {
                $imgField = "imagen_url_$i";
                if($producto->$imgField) {
                    $imagenes[] = $producto->getImageUrl($imgField);
                }
            }

            // Calcular precios con descuento
            $precioOriginal = $precioEnMonedaActual ? $precioEnMonedaActual->precio : 0;
            $precioConDescuento = $precioOriginal;
            $ahorro = 0;

            if ($producto->aplica_descuento && $producto->porcentaje_descuento > 0) {
                $descuento = $producto->porcentaje_descuento / 100;
                $precioConDescuento = $precioOriginal * (1 - $descuento);
                $ahorro = $precioOriginal - $precioConDescuento;
            }

            // Obtener otras monedas disponibles (excluyendo la actual)
            $otrasMonedas = $producto->precios
                ->where('is_active', true)
                ->where('moneda_id', '!=', $monedaActual->id)
                ->take(3)
                ->map(function($precio) {
                    return [
                        'simbolo' => $precio->moneda->simbolo,
                        'precio' => number_format($precio->precio, 0),
                        'codigo' => $precio->moneda->codigo_iso
                    ];
                });

            return (object)[
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion_corta' => Str::limit($producto->descripcion, 60),
                'tipo_producto' => $producto->tipo_producto,
                'categoria' => $producto->categoria,
                'sku' => $producto->sku,
                'url_recurso' => $producto->url_recurso,
                'aplica_descuento' => $producto->aplica_descuento,
                'porcentaje_descuento' => $producto->porcentaje_descuento,
                'is_active' => $producto->is_active,
                'imagenes' => $imagenes,
                'stock_actual' => $producto->getStockActualAttribute(),
                'es_fisico' => $producto->esFisico(),
                'es_digital' => $producto->esDigital(),

                // Datos de precio procesados
                'moneda_actual' => [
                    'codigo' => $monedaActual->codigo_iso,
                    'simbolo' => $monedaActual->simbolo,
                    'nombre' => $monedaActual->nombre,
                    'id' => $monedaActual->id
                ],
                'precio_original' => $precioOriginal,
                'precio_original_formateado' => $monedaActual->simbolo . ' ' . number_format($precioOriginal, 2),
                'precio_con_descuento' => $precioConDescuento,
                'precio_con_descuento_formateado' => $monedaActual->simbolo . ' ' . number_format($precioConDescuento, 2),
                'ahorro' => $ahorro,
                'ahorro_formateado' => $monedaActual->simbolo . ' ' . number_format($ahorro, 2),
                'otras_monedas' => $otrasMonedas,
                'tiene_descuento' => $producto->aplica_descuento && $producto->porcentaje_descuento > 0,
                'tiene_stock' => $producto->esFisico() ? ($producto->getStockActualAttribute() > 0) : true,
                'stock_disponible' => $producto->esFisico() ? $producto->getStockActualAttribute() : 'Ilimitado',
                'clase_stock' => $producto->esFisico()
                    ? ($producto->getStockActualAttribute() > 0 ? 'success' : 'danger')
                    : 'info',
                'icono_stock' => $producto->esFisico()
                    ? ($producto->getStockActualAttribute() > 0 ? 'fa-check-circle' : 'fa-times-circle')
                    : 'fa-infinity',
                'texto_stock' => $producto->esFisico()
                    ? ($producto->getStockActualAttribute() > 0 ? $producto->getStockActualAttribute() . ' disponibles' : 'Agotado')
                    : 'Stock ilimitado',

                // Información adicional útil
                'moneda_origen' => $precioEnMonedaActual && $precioEnMonedaActual->moneda
                    ? $precioEnMonedaActual->moneda->codigo_iso
                    : 'USD'
            ];
        });

        return $productos;
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
        // Obtener el carrito actual
        $carrito = $this->obtenerCarritoActual();

        // ============================================
        // MISMA LÓGICA DE MONEDA QUE EN INDEX
        // ============================================
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
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'stock'])
            ->active()
            ->findOrFail($id);

        // Procesar imágenes
        $images = [];
        for($i = 1; $i <= 5; $i++) {
            $imgField = "imagen_url_$i";
            if($producto->$imgField) {
                $images[] = $this->formatImageUrl($producto->$imgField);
            }
        }

        // Calcular datos del producto
        $productData = [
            'is_new' => $producto->created_at->diffInDays(now()) < 7,
            'stock_actual' => $producto->esFisico() ? $producto->getStockActualAttribute() : null,
            'precio_con_descuento' => $this->calcularPrecioConDescuento($producto),
            'rating' => $this->getProductRating($producto->id), // Puedes implementar esto según tu lógica
            'total_reviews' => $this->getTotalReviews($producto->id), // Implementar según tu lógica
            'caracteristicas' => $this->getCaracteristicasProducto($producto)
        ];

        return view('client.productos.detalle', compact('producto', 'images', 'productData'));
    }

    // Formatear URL de imagen
    private function formatImageUrl($url)
    {
        if (!$url) return null;

        // Limpiar la URL
        $url = str_replace(['/producto/', 'storage/storage/'], ['/', 'storage/'], $url);
        $url = str_replace('/storage/storage/', '/storage/', $url);

        // Si ya es URL completa, devolverla
        if (filter_var($url, FILTER_VALIDATE_URL) || str_starts_with($url, 'http')) {
            return $url;
        }

        // Formatear según el caso
        if (str_starts_with($url, 'storage/')) {
            return asset($url);
        }

        if (str_starts_with($url, '/storage/')) {
            return asset(substr($url, 1));
        }

        return asset('storage/' . ltrim($url, '/'));
    }

    // Calcular precio con descuento
    private function calcularPrecioConDescuento($producto)
    {
        if (!$producto->aplica_descuento || $producto->porcentaje_descuento <= 0) {
            return [
                'tiene_descuento' => false,
                'precio_original_local' => $producto->precioLocal,
                'precio_original_usd' => $producto->precioUSD,
                'precio_final_local' => $producto->precioLocal,
                'precio_final_usd' => $producto->precioUSD,
                'descuento' => 0,
                'porcentaje' => 0
            ];
        }

        $descuento = $producto->porcentaje_descuento / 100;
        $precioFinalLocal = $producto->precioLocal * (1 - $descuento);
        $precioFinalUSD = $producto->precioUSD * (1 - $descuento);

        return [
            'tiene_descuento' => true,
            'precio_original_local' => $producto->precioLocal,
            'precio_original_usd' => $producto->precioUSD,
            'precio_final_local' => $precioFinalLocal,
            'precio_final_usd' => $precioFinalUSD,
            'descuento' => $producto->precioLocal - $precioFinalLocal,
            'porcentaje' => $producto->porcentaje_descuento
        ];
    }

    // Obtener características del producto según su tipo
    private function getCaracteristicasProducto($producto)
    {
        $caracteristicas = [];

        if ($producto->esFisico()) {
            $caracteristicas = [
                'Tipo' => 'Producto físico',
                'SKU' => $producto->sku ?? 'No disponible',
                'Conexión' => 'Puede conectarse a cualquier dispositivo',
                'Orientación' => 'Diestro',
                'Garantía' => 'Producto original'
            ];

            // Agregar stock si está disponible
            if ($producto->stock) {
                $caracteristicas['Stock'] = $producto->stock->cantidad . ' unidades disponibles';
            }
        } else {
            $caracteristicas = [
                'Tipo' => 'Producto digital',
                'Entrega' => 'Descarga inmediata después de la compra',
                'Acceso' => 'Acceso para siempre',
                'Stock' => 'Ilimitado',
                'Formato' => 'Digital'
            ];
        }

        return $caracteristicas;
    }

    // Obtener rating del producto (implementa según tu lógica)
    private function getProductRating($productoId)
    {
        // TODO: Implementar lógica real de rating
        // Esto es solo un ejemplo
        return 4.5; // Rating de ejemplo
    }

    // Obtener total de reseñas (implementa según tu lógica)
    private function getTotalReviews($productoId)
    {
        // TODO: Implementar lógica real de reseñas
        // Esto es solo un ejemplo
        return 2; // Total de reseñas de ejemplo
    }
}
