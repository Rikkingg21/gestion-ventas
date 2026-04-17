<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CategoriasController extends Controller
{
    private function getViewData($extra = [])
    {
        return array_merge([
            'currentUser' => Auth::guard('admin')->user()
        ], $extra);
    }

    private function validateCategoria(Request $request, $categoria = null)
    {
        $rules = [
            'nombre' => [
                'required',
                'string',
                'max:100',
                $categoria ? Rule::unique('categorias')->ignore($categoria->id) : 'unique:categorias,nombre'
            ],
            'descripcion' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator;
        }

        return [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'is_active' => $request->boolean('is_active')
        ];
    }

    private function redirectWithMessage($message, $type = 'success')
    {
        return redirect()->route('admin.categorias.index')->with($type, $message);
    }

    public function index(Request $request)
    {
        $query = Categoria::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $categorias = $query->orderBy('nombre')->paginate(10);

        return view('admin.categorias.index', $this->getViewData(compact('categorias')));
    }

    public function create()
    {
        return view('admin.categorias.create', $this->getViewData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategoria($request);

        if ($validated instanceof \Illuminate\Contracts\Validation\Validator) {
            return redirect()->back()->withErrors($validated)->withInput();
        }

        Categoria::create($validated);

        return $this->redirectWithMessage('Categoría creada exitosamente.');
    }

    public function show(string $id)
    {
        $categoria = Categoria::with('productos')->findOrFail($id);

        return view('admin.categorias.show', $this->getViewData(compact('categoria')));
    }

    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        return view('admin.categorias.edit', $this->getViewData(compact('categoria')));
    }

    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $validated = $this->validateCategoria($request, $categoria);

        if ($validated instanceof \Illuminate\Contracts\Validation\Validator) {
            return redirect()->back()->withErrors($validated)->withInput();
        }

        $categoria->update($validated);

        return $this->redirectWithMessage('Categoría actualizada exitosamente.');
    }

    public function destroy(string $id)
    {
        $categoria = Categoria::findOrFail($id);

        if ($categoria->productos()->count() > 0) {
            return $this->redirectWithMessage(
                'No se puede eliminar la categoría porque tiene productos asociados.',
                'error'
            );
        }

        $categoria->delete();

        return $this->redirectWithMessage('Categoría eliminada exitosamente.');
    }
}
