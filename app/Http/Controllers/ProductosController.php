<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Helpers\MonedaHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        try {
            $categorias = Cache::remember('categorias_activas_conteo', now()->addHours(6), function () {
                return Categoria::where('is_active', true)
                    ->withCount('productos')
                    ->orderBy('nombre')
                    ->get();
            });

            $monedaActual = MonedaHelper::getMonedaActual();
            $carritoData = CarritoController::carritoSidebar();

            return view('client.productos.index', compact(
                'categorias',
                'monedaActual',
                'carritoData'
            ));

        } catch (\Exception $e) {
            $carritoData = CarritoController::carritoSidebar();

            return view('client.productos.index', [
                'categorias' => collect([]),
                'monedaActual' => MonedaHelper::getMonedaActual(),
                'carritoData' => $carritoData,
                'error' => 'Error al cargar los productos'
            ]);
        }
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'stock'])
            ->active()
            ->findOrFail($id);

        $monedaActual = MonedaHelper::getMonedaActual();
        $precioInfo = MonedaHelper::getPrecioProductoEnMoneda($producto, $monedaActual->id);
        $carritoData = CarritoController::carritoSidebar();

        return view('client.productos.detalle', compact(
            'producto',
            'monedaActual',
            'precioInfo',
            'carritoData'
        ));
    }
}
