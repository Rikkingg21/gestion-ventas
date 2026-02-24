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
            'aplica_descuento' => 'boolean',
            'tipo_producto' => 'required|in:fisico,digital',
            'imagenes' => 'nullable|array|max:5',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean'
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

        $data = $request->all();
        $data['aplica_descuento'] = $request->has('aplica_descuento');
        $data['is_active'] = $request->has('is_active');

        // Manejar la subida de múltiples imágenes
        if ($request->hasFile('imagenes')) {
            $imagenes = $request->file('imagenes');
            $contador = 1;

            foreach ($imagenes as $imagen) {
                if ($contador > 5) break; // Máximo 5 imágenes

                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
                $imagen->move(public_path('storage/productos'), $nombreImagen);

                $data['imagen_url_' . $contador] = 'storage/productos/' . $nombreImagen;
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

    public function show($id)
    {
        $producto = Producto::with('categoria', 'stock')->findOrFail($id);

        // Obtener todas las imágenes del producto
        $imagenes = [];
        for ($i = 1; $i <= 5; $i++) {
            $campo = 'imagen_url_' . $i;
            if ($producto->$campo) {
                $imagenes[] = $producto->$campo;
            }
        }

        return view('admin.productos.show', compact('producto', 'imagenes'));
    }

    public function edit($id)
    {
        $producto = Producto::with('stock')->findOrFail($id);
        $categorias = Categoria::where('is_active', true)->orderBy('nombre')->get();

        // Obtener las imágenes existentes
        $imagenesExistentes = [];
        for ($i = 1; $i <= 5; $i++) {
            $campo = 'imagen_url_' . $i;
            if ($producto->$campo) {
                $imagenesExistentes[$i] = $producto->$campo;
            }
        }

        return view('admin.productos.edit', compact('producto', 'categorias', 'imagenesExistentes'));
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
            'aplica_descuento' => 'boolean',
            'tipo_producto' => 'required|in:fisico,digital',
            'imagenes_nuevas' => 'nullable|array|max:5',
            'imagenes_nuevas.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'imagenes_eliminar' => 'nullable|array',
            'imagenes_eliminar.*' => 'integer|between:1,5',
            'is_active' => 'boolean'
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

        $data = $request->all();
        $data['aplica_descuento'] = $request->has('aplica_descuento');
        $data['is_active'] = $request->has('is_active');

        // ===== Manejar eliminación de imágenes existentes =====
        // Inicializar todas las posiciones de imagen como null para luego sobrescribir
        for ($i = 1; $i <= 5; $i++) {
            $data['imagen_url_' . $i] = null;
        }

        // Primero, mantener las imágenes existentes que NO se eliminarán
        for ($i = 1; $i <= 5; $i++) {
            $campo = 'imagen_url_' . $i;
            if ($producto->$campo) {
                $data[$campo] = $producto->$campo;
            }
        }

        // Procesar imágenes a eliminar
        $imagenesEliminar = [];

        // Verificar si hay imágenes para eliminar
        if ($request->has('imagenes_eliminar')) {
            // Si viene como string JSON (del formulario)
            if (is_string($request->imagenes_eliminar) && !empty($request->imagenes_eliminar)) {
                $imagenesEliminar = json_decode($request->imagenes_eliminar, true) ?? [];
            }
            // Si viene como array
            elseif (is_array($request->imagenes_eliminar)) {
                $imagenesEliminar = $request->imagenes_eliminar;
            }
        }

        // Eliminar las imágenes marcadas
        if (!empty($imagenesEliminar)) {
            foreach ($imagenesEliminar as $posicion) {
                $campo = 'imagen_url_' . $posicion;
                if ($producto->$campo) {
                    // Eliminar archivo físico
                    $rutaCompleta = public_path($producto->$campo);
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                    // Eliminar referencia en BD
                    $data[$campo] = null;
                }
            }
        }

        // ===== Manejar subida de nuevas imágenes =====
        $erroresImagenes = [];

        if ($request->hasFile('imagenes_nuevas')) {
            // Encontrar posiciones disponibles (1-5)
            $posicionesOcupadas = [];
            for ($i = 1; $i <= 5; $i++) {
                $campo = 'imagen_url_' . $i;
                if (!empty($data[$campo])) {
                    $posicionesOcupadas[] = $i;
                }
            }

            $posicionesDisponibles = array_values(array_diff(range(1, 5), $posicionesOcupadas));
            $nuevasImagenes = $request->file('imagenes_nuevas');

            // Verificar si hay posiciones disponibles
            if (empty($posicionesDisponibles)) {
                return redirect()->back()
                    ->withErrors(['imagenes_nuevas' => 'No hay espacio para más imágenes. Máximo 5 imágenes permitidas.'])
                    ->withInput();
            }

            $contador = 0;
            foreach ($nuevasImagenes as $index => $imagen) {
                // Verificar que todavía hay posiciones disponibles
                if (!isset($posicionesDisponibles[$contador])) {
                    $erroresImagenes[] = "No hay suficiente espacio para todas las imágenes. Algunas no se pudieron subir.";
                    break;
                }

                $posicion = $posicionesDisponibles[$contador];

                try {
                    // Validar la imagen individualmente (aunque ya se validó con las reglas)
                    if (!$imagen->isValid()) {
                        $erroresImagenes[] = "La imagen " . ($index + 1) . " no es válida.";
                        $contador++;
                        continue;
                    }

                    $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                    // Intentar mover la imagen
                    if (!$imagen->move(public_path('storage/productos'), $nombreImagen)) {
                        $erroresImagenes[] = "Error al subir la imagen " . ($index + 1) . ".";
                    } else {
                        $data['imagen_url_' . $posicion] = 'storage/productos/' . $nombreImagen;
                    }

                    $contador++;
                } catch (\Exception $e) {
                    $erroresImagenes[] = "Error al procesar la imagen " . ($index + 1) . ": " . $e->getMessage();
                }
            }
        }

        // Si hay errores con las imágenes, redirigir con los errores
        if (!empty($erroresImagenes)) {
            return redirect()->back()
                ->withErrors(['imagenes' => implode(' ', $erroresImagenes)])
                ->withInput();
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
            if ($producto->$campo && file_exists(public_path($producto->$campo))) {
                unlink(public_path($producto->$campo));
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

    /**
     * Eliminar una imagen específica vía AJAX
     */
    public function eliminarImagen(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $posicion = $request->posicion;

            \Log::info('Eliminando imagen', [
                'producto_id' => $id,
                'posicion' => $posicion,
                'request_all' => $request->all()
            ]);

            if ($posicion >= 1 && $posicion <= 5) {
                $campo = 'imagen_url_' . $posicion;

                if ($producto->$campo) {
                    // Eliminar archivo físico
                    $rutaCompleta = public_path($producto->$campo);
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }

                    // Eliminar referencia en BD
                    $producto->$campo = null;
                    $producto->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Imagen eliminada correctamente'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'La imagen no existe'
                    ], 404);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Posición de imagen inválida'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Error al eliminar imagen', [
                'error' => $e->getMessage(),
                'producto_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}
