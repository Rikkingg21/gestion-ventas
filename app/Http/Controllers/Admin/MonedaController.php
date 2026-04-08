<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Moneda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MonedaController extends Controller
{
    public function index(Request $request)
    {
        $query = Moneda::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pais', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo_iso', 'like', "%{$search}%")
                  ->orWhere('pais_code', 'like', "%{$search}%");
            });
        }

        $monedas = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.moneda.index', compact('monedas'));
    }

    public function create()
    {
        return view('admin.moneda.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pais' => 'required|string|max:100',
            'pais_code' => 'nullable|string|max:10',
            'nombre' => 'required|string|max:100',
            'codigo_iso' => 'required|string|max:3|unique:monedas,codigo_iso',
            'simbolo' => 'required|string|max:5',
            'tasa_cambio_usd' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'aceptar_pagos' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Moneda::create($request->all());
            return redirect()->route('admin.moneda.index')->with('success', 'Moneda creada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al crear moneda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la moneda')->withInput();
        }
    }

    public function edit(Moneda $moneda)
    {
        return view('admin.moneda.edit', compact('moneda'));
    }

    public function update(Request $request, Moneda $moneda)
    {
        $validator = Validator::make($request->all(), [
            'pais' => 'required|string|max:100',
            'pais_code' => 'nullable|string|max:10',
            'nombre' => 'required|string|max:100',
            'codigo_iso' => 'required|string|max:3|unique:monedas,codigo_iso,' . $moneda->id,
            'simbolo' => 'required|string|max:5',
            'tasa_cambio_usd' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'aceptar_pagos' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $moneda->update($request->all());
            return redirect()->route('admin.moneda.index')->with('success', 'Moneda actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar moneda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la moneda')->withInput();
        }
    }

    public function destroy(Moneda $moneda)
    {
        try {
            $moneda->delete();
            return redirect()->route('admin.moneda.index')->with('success', 'Moneda eliminada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar moneda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar la moneda');
        }
    }
}
