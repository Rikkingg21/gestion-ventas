<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    private function getViewData($extra = [])
    {
        return array_merge([
            'currentUser' => Auth::guard('admin')->user()
        ], $extra);
    }

    public function index()
    {
        $modules = Module::with(['children', 'parent', 'permisos'])
                        ->orderBy('order_position')
                        ->orderBy('name')
                        ->get();

        return view('admin.modules.index', $this->getViewData(compact('modules')));
    }

    public function create()
    {
        $parentModules = Module::whereNull('parent_id')->orderBy('name')->get();
        $action = 'crear';

        return view('admin.modules.create', $this->getViewData(compact('parentModules', 'action')));
    }

    public function store(Request $request)
    {
        $validated = $this->validateModule($request);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        Module::create($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Módulo creado correctamente.');
    }

    public function edit(Module $module)
    {
        $parentModules = Module::whereNull('parent_id')
                               ->where('id', '!=', $module->id)
                               ->whereNotIn('id', $module->children->pluck('id'))
                               ->orderBy('name')
                               ->get();

        return view('admin.modules.edit', $this->getViewData(compact('module', 'parentModules')));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $this->validateModule($request, $module);

        // Validaciones de parentesco
        if ($request->parent_id == $module->id) {
            return back()->withInput()->withErrors(['parent_id' => 'Un módulo no puede ser padre de sí mismo.']);
        }

        if ($module->children()->where('id', $request->parent_id)->exists()) {
            return back()->withInput()->withErrors(['parent_id' => 'No puedes asignar un submódulo como módulo padre.']);
        }

        $module->update($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Módulo actualizado correctamente.');
    }

    public function destroy(Module $module)
    {
        if ($module->children()->count() > 0 || $module->permisos()->count() > 0) {
            $error = $module->children()->count() > 0
                ? 'No se puede eliminar un módulo que tiene submódulos.'
                : 'No se puede eliminar un módulo que tiene permisos asociados.';

            return redirect()->route('admin.modules.index')->with('error', $error);
        }

        $module->delete();

        return redirect()->route('admin.modules.index')->with('success', 'Módulo eliminado correctamente.');
    }

    private function validateModule(Request $request, $module = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:modules,slug' . ($module ? ',' . $module->id : ''),
            'icon' => 'nullable|string|max:50',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:modules,id',
            'order_position' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ];

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->has('is_active');
        $validated['order_position'] = $validated['order_position'] ?? 0;

        return $validated;
    }
}
