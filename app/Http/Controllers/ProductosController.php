<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

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

        return view('client.productos.index', compact('categorias', 'productos'));
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

        return view('client.productos.detalle', compact('producto'));
    }
}
