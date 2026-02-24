<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductosController extends Controller
{
    public function index()
    {
        // Obtener categorías con sus productos activos
        $categorias = Categoria::where('is_active', true)
            ->with(['productos' => function($query) {
                $query->active()
                      ->with('stock')
                      ->orderBy('nombre');
            }])
            ->orderBy('nombre')
            ->get();

        return view('client.productos.index', compact('categorias'));
    }

    public function funcion()
    {
        return view('nosotros');
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'stock'])
            ->active()
            ->findOrFail($id);

        return view('client.productos.producto-detalle', compact('producto'));
    }
}
