<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Moneda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MonedaController extends Controller
{
    private function getViewData($extra = [])
    {
        return array_merge([
            'currentUser' => Auth::guard('admin')->user()
        ], $extra);
    }

    private function validateMoneda(Request $request, $moneda = null)
    {
        $rules = [
            'pais' => 'required|string|max:100',
            'pais_code' => 'nullable|string|max:10',
            'nombre' => 'required|string|max:100',
            'codigo_iso' => [
                'required',
                'string',
                'max:3',
                $moneda ? Rule::unique('monedas')->ignore($moneda->id) : 'unique:monedas,codigo_iso'
            ],
            'simbolo' => 'required|string|max:5',
            'tasa_cambio_usd' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'aceptar_pagos' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator;
        }

        return $validator->validated();
    }

    private function executeWithLogging(callable $callback, $successMessage, $errorMessage)
    {
        try {
            $callback();
            return redirect()->route('admin.moneda.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error($errorMessage . ': ' . $e->getMessage());
            return redirect()->back()->with('error', $errorMessage)->withInput();
        }
    }

    public function index(Request $request)
    {
        $query = Moneda::query();

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

        return view('admin.moneda.index', $this->getViewData(compact('monedas')));
    }

    public function create()
    {
        return view('admin.moneda.create', $this->getViewData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateMoneda($request);

        if ($validated instanceof \Illuminate\Contracts\Validation\Validator) {
            return redirect()->back()->withErrors($validated)->withInput();
        }

        return $this->executeWithLogging(
            fn() => Moneda::create($validated),
            'Moneda creada exitosamente',
            'Error al crear la moneda'
        );
    }

    public function edit(Moneda $moneda)
    {
        return view('admin.moneda.edit', $this->getViewData(compact('moneda')));
    }

    public function update(Request $request, Moneda $moneda)
    {
        $validated = $this->validateMoneda($request, $moneda);

        if ($validated instanceof \Illuminate\Contracts\Validation\Validator) {
            return redirect()->back()->withErrors($validated)->withInput();
        }

        return $this->executeWithLogging(
            fn() => $moneda->update($validated),
            'Moneda actualizada exitosamente',
            'Error al actualizar la moneda'
        );
    }

    public function destroy(Moneda $moneda)
    {
        return $this->executeWithLogging(
            fn() => $moneda->delete(),
            'Moneda eliminada exitosamente',
            'Error al eliminar la moneda'
        );
    }
}
