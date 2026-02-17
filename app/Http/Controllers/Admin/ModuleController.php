<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todos los módulos con sus relaciones
        $modules = Module::with(['children', 'parent'])
                        ->orderBy('order_position')
                        ->orderBy('name')
                        ->get();

        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener módulos padre para el selector
        $parentModules = Module::whereNull('parent_id')
                            ->orderBy('name')
                            ->get();

        return view('admin.modules.create', compact('parentModules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:modules,slug|max:255',
            'icon' => 'nullable|string|max:50',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:modules,id',
            'order_position' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Generar slug si no se proporciona
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Establecer valores por defecto
        $validated['is_active'] = $request->has('is_active');
        $validated['order_position'] = $validated['order_position'] ?? 0;

        Module::create($validated);

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Módulo creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Module $module)
    {
        // Obtener módulos padre (excluyendo el actual y sus hijos)
        $parentModules = Module::whereNull('parent_id')
                               ->where('id', '!=', $module->id)
                               ->whereNotIn('id', $module->children->pluck('id'))
                               ->orderBy('name')
                               ->get();

        return view('admin.modules.edit', compact('module', 'parentModules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug,' . $module->id,
            'icon' => 'nullable|string|max:50',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:modules,id',
            'order_position' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Validar que parent_id no sea el mismo módulo
        if ($request->parent_id == $module->id) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'Un módulo no puede ser padre de sí mismo.']);
        }

        // Validar que no se asigne un hijo como padre
        if ($module->children()->where('id', $request->parent_id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => 'No puedes asignar un submódulo como módulo padre.']);
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['order_position'] = $validated['order_position'] ?? 0;

        $module->update($validated);

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Módulo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        // Verificar si tiene hijos
        if ($module->children()->count() > 0) {
            return redirect()
                ->route('admin.modules.index')
                ->with('error', 'No se puede eliminar un módulo que tiene submódulos.');
        }

        // Verificar si tiene permisos asociados
        if ($module->permissions()->count() > 0) {
            return redirect()
                ->route('admin.modules.index')
                ->with('error', 'No se puede eliminar un módulo que tiene permisos asociados.');
        }

        $module->delete();

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Módulo eliminado correctamente.');
    }
}
