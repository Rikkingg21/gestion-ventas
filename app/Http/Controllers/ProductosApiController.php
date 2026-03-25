<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Helpers\MonedaHelper;
use App\Helpers\ImagenesHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductosApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Producto::with(['categoria', 'precios.moneda', 'stock'])
                ->where('is_active', true);

            // Aplicar filtros
            if ($request->has('categoria') && $request->categoria !== 'all') {
                $query->where('categoria_id', $request->categoria);
            }

            if ($request->has('tipo') && $request->tipo !== 'all') {
                $query->where('tipo_producto', $request->tipo);
            }

            if ($request->has('search') && !empty($request->search)) {
                $query->where('nombre', 'like', '%' . $request->search . '%');
            }

            // Paginación
            $perPage = $request->get('per_page', 12);
            $productos = $query->orderBy('nombre')->paginate($perPage);

            // Obtener moneda actual
            $monedaActual = MonedaHelper::getMonedaActual();

            // Transformar productos usando helpers
            $productosTransformados = $productos->through(function($producto) use ($monedaActual) {
                return $this->transformProducto($producto, $monedaActual);
            });

            return response()->json([
                'success' => true,
                'data' => $productosTransformados->items(),
                'meta' => [
                    'current_page' => $productos->currentPage(),
                    'last_page' => $productos->lastPage(),
                    'per_page' => $productos->perPage(),
                    'total' => $productos->total(),
                    'moneda_actual' => [
                        'id' => $monedaActual->id,
                        'codigo' => $monedaActual->codigo_iso,
                        'simbolo' => $monedaActual->simbolo,
                        'nombre' => $monedaActual->nombre
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $producto = Producto::with(['categoria', 'precios.moneda', 'stock'])
                ->where('is_active', true)
                ->findOrFail($id);

            $monedaActual = MonedaHelper::getMonedaActual();
            $productoTransformado = $this->transformProductoDetalle($producto, $monedaActual);

            return response()->json([
                'success' => true,
                'data' => $productoTransformado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }
    private function transformProducto($producto, $monedaActual)
    {
    // Usar helpers para imágenes y precios
    $imagenes = ImagenesHelper::getImagenesProducto($producto);
    $precioInfo = MonedaHelper::getPrecioProductoEnMoneda($producto, $monedaActual->id);

    return [
        'id' => $producto->id,
        'nombre' => $producto->nombre,
        'descripcion_corta' => Str::limit($producto->descripcion, 60),
        'tipo' => $producto->tipo_producto,
        'categoria' => [
            'id' => $producto->categoria_id,
            'nombre' => $producto->categoria->nombre ?? 'Sin categoría'
        ],
        // AGREGAR BADGES
        'badges' => [
            'es_digital' => $producto->esDigital(),
            'tiene_descuento' => $precioInfo['tiene_descuento'],
            'porcentaje_descuento' => $precioInfo['porcentaje_descuento']
        ],
        'imagenes' => [
            'principal' => $imagenes->first() ?? ImagenesHelper::getDefaultImage(),
            'todas' => $imagenes,
            'cantidad' => count($imagenes)
        ],
        'precios' => [
            'original' => $precioInfo['precio_original'],
            'original_formateado' => $precioInfo['precio_original_formateado'],
            'con_descuento' => $precioInfo['precio_con_descuento'],
            'con_descuento_formateado' => $precioInfo['precio_con_descuento_formateado'],
            'tiene_descuento' => $precioInfo['tiene_descuento'],
            'porcentaje_descuento' => $precioInfo['porcentaje_descuento'],
            'moneda' => $precioInfo['moneda']
        ],
        'stock' => [
            'actual' => $producto->stock_actual,
            'texto' => $this->getTextoStock($producto),
            'icono' => $this->getIconoStock($producto),
            'clase' => $this->getClaseStock($producto)
        ],
        'sku' => $producto->sku,
        'url_recurso' => $producto->url_recurso
    ];
    }

    private function transformProductoDetalle($producto, $monedaActual)
    {
        $data = $this->transformProducto($producto, $monedaActual);

        // Agregar más detalles para la vista individual
        $data['descripcion_completa'] = $producto->descripcion;
        $data['imagenes_metadata'] = ImagenesHelper::getImagenesConMetadata($producto);
        $data['caracteristicas'] = $this->getCaracteristicasProducto($producto);

        return $data;
    }

    private function getTextoStock($producto)
    {
        if ($producto->esDigital()) return 'Stock digital';
        if ($producto->stock_actual > 10) return $producto->stock_actual . ' disponibles';
        if ($producto->stock_actual > 0) return '¡Últimos ' . $producto->stock_actual . '!';
        return 'Agotado';
    }

    private function getIconoStock($producto)
    {
        if ($producto->esDigital()) return 'fa-infinity';
        if ($producto->stock_actual > 0) return 'fa-check-circle';
        return 'fa-times-circle';
    }

    private function getClaseStock($producto)
    {
        if ($producto->esDigital()) return 'info';
        if ($producto->stock_actual > 10) return 'success';
        if ($producto->stock_actual > 0) return 'warning';
        return 'danger';
    }

    private function getCaracteristicasProducto($producto)
    {
        if ($producto->esFisico()) {
            return [
                ['label' => 'Tipo', 'valor' => 'Producto físico'],
                ['label' => 'SKU', 'valor' => $producto->sku ?? 'No disponible'],
                ['label' => 'Garantía', 'valor' => 'Producto original'],
                ['label' => 'Stock', 'valor' => $producto->stock_actual . ' unidades']
            ];
        }

        return [
            ['label' => 'Tipo', 'valor' => 'Producto digital'],
            ['label' => 'Entrega', 'valor' => 'Descarga inmediata'],
            ['label' => 'Acceso', 'valor' => 'Para siempre'],
            ['label' => 'Stock', 'valor' => 'Ilimitado']
        ];
    }
}
