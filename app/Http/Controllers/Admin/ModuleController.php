<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    public function index()
    {
        // El middleware ya verificó admin:leer,modules
        $user = Auth::guard('admin')->user();

        $modules = Module::with(['children', 'parent', 'permisos'])
                        ->orderBy('order_position')
                        ->orderBy('name')
                        ->get();

        return view('admin.modules.index', compact('modules'));
    }

    public function create()
    {
        // El middleware ya verificó admin:crear,modules
        $parentModules = Module::whereNull('parent_id')
                            ->orderBy('name')
                            ->get();

        return view('admin.modules.create', compact('parentModules'));
    }

    public function store(Request $request)
    {
        // El middleware ya verificó admin:crear,modules

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

    public function edit(Module $module)
    {
        // El middleware ya verificó admin:actualizar,modules

        $parentModules = Module::whereNull('parent_id')
                               ->where('id', '!=', $module->id)
                               ->whereNotIn('id', $module->children->pluck('id'))
                               ->orderBy('name')
                               ->get();

        return view('admin.modules.edit', compact('module', 'parentModules'));
    }

    public function update(Request $request, Module $module)
    {
        // El middleware ya verificó admin:actualizar,modules

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

    public function destroy(Module $module)
    {
        // El middleware ya verificó admin:eliminar,modules

        // Verificar si tiene hijos
        if ($module->children()->count() > 0) {
            return redirect()
                ->route('admin.modules.index')
                ->with('error', 'No se puede eliminar un módulo que tiene submódulos.');
        }

        // Verificar si tiene permisos asociados
        if ($module->permisos()->count() > 0) {
            return redirect()
                ->route('admin.modules.index')
                ->with('error', 'No se puede eliminar un módulo que tiene permisos asociados.');
        }

        $module->delete();

        return redirect()
            ->route('admin.modules.index')
            ->with('success', 'Módulo eliminado correctamente.');
    }

    // Método show opcional
    public function show(Module $module)
    {
        // El middleware ya verificó admin:leer,modules
        return view('admin.modules.show', compact('module'));
    }
}
