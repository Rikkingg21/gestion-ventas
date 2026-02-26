<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoStock;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria', 'stock');

        // Búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $query->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        // Filtro por categoría
        if ($request->has('categoria') && !empty($request->categoria)) {
            $query->where('categoria_id', $request->categoria);
        }

        // Filtro por tipo
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo_producto', $request->tipo);
        }

        $productos = $query->orderBy('nombre')->paginate(10);
        $categorias = Categoria::where('is_active', true)->orderBy('nombre')->get();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::where('is_active', true)->orderBy('nombre')->get();
        return view('admin.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $rules = [
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precioUSD' => 'required|numeric|min:0',
            'precioLocal' => 'required|numeric|min:0',
            'aplica_descuento' => 'sometimes|boolean',
            'porcentaje_descuento' => 'nullable|required_if:aplica_descuento,1|numeric|min:0|max:100',
            'tipo_producto' => 'required|in:fisico,digital',
            'imagenes' => 'nullable|array|max:5',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'sometimes|boolean'
        ];

        // Reglas específicas según tipo
        if ($request->tipo_producto === 'fisico') {
            $rules['sku'] = 'required|string|unique:productos,sku';
            $rules['stock'] = 'required|integer|min:0';
            $rules['stock_minimo'] = 'nullable|integer|min:0';
        } else {
            $rules['url_recurso'] = 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['imagenes', 'stock', 'stock_minimo']);
        $data['aplica_descuento'] = $request->boolean('aplica_descuento');
        $data['is_active'] = $request->boolean('is_active');

        // Manejar la subida de múltiples imágenes
        if ($request->hasFile('imagenes')) {
            $imagenes = $request->file('imagenes');
            $contador = 1;

            foreach ($imagenes as $imagen) {
                if ($contador > 5) break;

                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                // Usar Storage para mejor manejo
                $path = $imagen->storeAs('public/productos', $nombreImagen);

                // Guardar la ruta relativa para usar con asset()
                $data['imagen_url_' . $contador] = Storage::url($path);
                $contador++;
            }
        }

        $producto = Producto::create($data);

        // Crear registro de stock si es producto físico
        if ($request->tipo_producto === 'fisico') {
            ProductoStock::create([
                'producto_id' => $producto->id,
                'cantidad' => $request->stock,
                'stock_minimo' => $request->stock_minimo ?? 0,
                'stock_maximo' => null,
                'ubicacion' => $request->ubicacion ?? null
            ]);
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit($id)
    {
        $producto = Producto::with('stock')->findOrFail($id);
        $categorias = Categoria::where('is_active', true)->orderBy('nombre')->get();

        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $rules = [
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precioUSD' => 'required|numeric|min:0',
            'precioLocal' => 'required|numeric|min:0',
            'aplica_descuento' => 'sometimes|boolean',
            'porcentaje_descuento' => 'nullable|required_if:aplica_descuento,1|numeric|min:0|max:100',
            'tipo_producto' => 'required|in:fisico,digital',
            'imagenes_nuevas' => 'nullable|array|max:5',
            'imagenes_nuevas.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'imagenes_eliminar' => 'nullable|array',
            'imagenes_eliminar.*' => 'integer|between:1,5',
            'is_active' => 'sometimes|boolean'
        ];

        // Reglas específicas según tipo
        if ($request->tipo_producto === 'fisico') {
            $rules['sku'] = [
                'required',
                'string',
                Rule::unique('productos')->ignore($producto->id)
            ];
            $rules['stock'] = 'required|integer|min:0';
            $rules['stock_minimo'] = 'nullable|integer|min:0';
        } else {
            $rules['url_recurso'] = 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['imagenes_nuevas', 'imagenes_eliminar', 'stock', 'stock_minimo']);
        $data['aplica_descuento'] = $request->boolean('aplica_descuento');
        $data['is_active'] = $request->boolean('is_active');

        // Procesar imágenes a eliminar
        if ($request->has('imagenes_eliminar') && is_array($request->imagenes_eliminar)) {
            foreach ($request->imagenes_eliminar as $posicion) {
                $campo = 'imagen_url_' . $posicion;
                if ($producto->$campo) {
                    // Eliminar archivo físico
                    $ruta = str_replace('/storage/', 'public/', $producto->$campo);
                    Storage::delete($ruta);

                    // Eliminar referencia
                    $data[$campo] = null;
                }
            }
        } else {
            // Mantener imágenes existentes si no se eliminan
            for ($i = 1; $i <= 5; $i++) {
                $campo = 'imagen_url_' . $i;
                if ($producto->$campo) {
                    $data[$campo] = $producto->$campo;
                }
            }
        }

        // Subir nuevas imágenes
        if ($request->hasFile('imagenes_nuevas')) {
            $posicionesDisponibles = [];
            for ($i = 1; $i <= 5; $i++) {
                $campo = 'imagen_url_' . $i;
                if (empty($data[$campo])) {
                    $posicionesDisponibles[] = $i;
                }
            }

            $nuevasImagenes = $request->file('imagenes_nuevas');
            $contador = 0;

            foreach ($nuevasImagenes as $imagen) {
                if ($contador >= count($posicionesDisponibles)) break;

                $posicion = $posicionesDisponibles[$contador];
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                $path = $imagen->storeAs('public/productos', $nombreImagen);
                $data['imagen_url_' . $posicion] = Storage::url($path);

                $contador++;
            }
        }

        // Actualizar el producto
        $producto->update($data);

        // Actualizar stock si es producto físico
        if ($request->tipo_producto === 'fisico') {
            if ($producto->stock) {
                $producto->stock->update([
                    'cantidad' => $request->stock,
                    'stock_minimo' => $request->stock_minimo ?? 0,
                ]);
            } else {
                ProductoStock::create([
                    'producto_id' => $producto->id,
                    'cantidad' => $request->stock,
                    'stock_minimo' => $request->stock_minimo ?? 0,
                ]);
            }
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        // Eliminar todas las imágenes asociadas
        for ($i = 1; $i <= 5; $i++) {
            $campo = 'imagen_url_' . $i;
            if ($producto->$campo) {
                $ruta = str_replace('/storage/', 'public/', $producto->$campo);
                Storage::delete($ruta);
            }
        }

        // Eliminar stock si existe
        if ($producto->stock) {
            $producto->stock->delete();
        }

        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
