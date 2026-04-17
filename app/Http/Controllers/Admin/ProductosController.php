<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\ProductoStock;
use App\Models\Categoria;
use App\Models\Moneda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProductosController extends Controller
{
    private function getViewData($extra = [])
    {
        return array_merge([
            'currentUser' => Auth::guard('admin')->user()
        ], $extra);
    }
    public function index(Request $request)
    {
        $query = Producto::with([
            'categoria',
            'stock',
            'precios' => function($query) {
                $query->with('moneda')->where('is_active', true);
            }
        ]);

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

        // Obtener monedas para referencia (opcional)
        $monedas = Moneda::where('is_active', true)->get();

        return view('admin.productos.index', compact('productos', 'categorias', 'monedas'), $this->getViewData(compact('productos')));
    }

    public function create()
    {
        $categorias = Categoria::where('is_active', true)->orderBy('nombre')->get();
        $monedas = Moneda::where('is_active', true)->get();

        return view('admin.productos.create', compact('categorias', 'monedas'));
    }

    public function store(Request $request)
    {
        $rules = [
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
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

        // Validar que al menos la moneda USD tenga precio
        $precioUSD = $request->input('precio_USD');
        if (!$precioUSD && $precioUSD !== '0') {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('precio_USD', 'El precio en USD es obligatorio.');

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Preparar datos del producto
        $data = $request->except(['imagenes', 'stock', 'stock_minimo']);
        $data['aplica_descuento'] = $request->boolean('aplica_descuento');
        $data['is_active'] = $request->boolean('is_active');

        // Crear el producto primero (necesitamos el ID para posibles nombres de imagen)
        $producto = Producto::create($data);

        // Manejar la subida de múltiples imágenes
        if ($request->hasFile('imagenes')) {
            $imagenes = $request->file('imagenes');
            $contador = 1;

            foreach ($imagenes as $imagen) {
                if ($contador > 5) break;

                // Crear nombre único para la imagen
                $extension = $imagen->getClientOriginalExtension();
                $nombreImagen = time() . '_' . uniqid() . '.' . $extension;

                // Ruta completa donde se guardará: public/storage/productos/
                $rutaDestino = public_path('storage/productos');

                // Crear el directorio si no existe
                if (!file_exists($rutaDestino)) {
                    mkdir($rutaDestino, 0755, true);
                }

                // Mover la imagen a la carpeta deseada
                $imagen->move($rutaDestino, $nombreImagen);

                // Guardar la ruta relativa en la base de datos
                $data['imagen_url_' . $contador] = 'storage/productos/' . $nombreImagen;

                $contador++;
            }

            // Actualizar el producto con las rutas de las imágenes
            $producto->update($data);
        }

        // Guardar precios en la tabla producto_precios
        $monedas = Moneda::where('is_active', true)->get();

        foreach ($monedas as $moneda) {
            $campoPrecio = 'precio_' . $moneda->codigo_iso;
            $precio = $request->input($campoPrecio);

            if ($precio !== null && $precio !== '') {
                ProductoPrecio::create([
                    'producto_id' => $producto->id,
                    'moneda_id' => $moneda->id,
                    'precio' => $precio,
                    'is_active' => true
                ]);
            }
        }

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
        $producto = Producto::with(['stock', 'precios.moneda'])->findOrFail($id);
        $categorias = Categoria::where('is_active', true)->orderBy('nombre')->get();
        $monedas = Moneda::where('is_active', true)->get();

        return view('admin.productos.edit', compact('producto', 'categorias', 'monedas'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $rules = [
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'aplica_descuento' => 'sometimes|boolean',
            'porcentaje_descuento' => 'nullable|required_if:aplica_descuento,1|numeric|min:0|max:100',
            'tipo_producto' => 'required|in:fisico,digital',
            'imagenes_nuevas' => 'nullable|array|max:5',
            'imagenes_nuevas.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'sometimes|boolean'
        ];

        // Validar que al menos la moneda USD tenga precio
        $precioUSD = $request->input('precio_USD');
        if (!$precioUSD && $precioUSD !== '0') {
            $validator = Validator::make($request->all(), []);
            $validator->errors()->add('precio_USD', 'El precio en USD es obligatorio.');

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

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
        if ($request->has('imagenes_eliminar') && !empty($request->imagenes_eliminar)) {
            $imagenesEliminar = json_decode($request->imagenes_eliminar, true);
            if (is_array($imagenesEliminar)) {
                foreach ($imagenesEliminar as $posicion) {
                    $campo = 'imagen_url_' . $posicion;
                    if ($producto->$campo) {
                        // Eliminar archivo físico
                        $rutaCompleta = public_path($producto->$campo);
                        if (file_exists($rutaCompleta)) {
                            unlink($rutaCompleta);
                        }

                        // Eliminar referencia en la base de datos
                        $data[$campo] = null;
                    }
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
            // Encontrar posiciones disponibles (campos null)
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

                // Crear nombre único para la imagen
                $extension = $imagen->getClientOriginalExtension();
                $nombreImagen = time() . '_' . uniqid() . '.' . $extension;

                // Ruta completa donde se guardará: public/storage/productos/
                $rutaDestino = public_path('storage/productos');

                // Crear el directorio si no existe
                if (!file_exists($rutaDestino)) {
                    mkdir($rutaDestino, 0755, true);
                }

                // Mover la imagen a la carpeta deseada
                $imagen->move($rutaDestino, $nombreImagen);

                // Guardar la ruta relativa en la base de datos
                $data['imagen_url_' . $posicion] = 'storage/productos/' . $nombreImagen;

                $contador++;
            }
        }

        // Actualizar el producto
        $producto->update($data);

        // Actualizar precios en la tabla producto_precios
        $monedas = Moneda::where('is_active', true)->get();

        foreach ($monedas as $moneda) {
            $campoPrecio = 'precio_' . $moneda->codigo_iso;
            $nuevoPrecio = $request->input($campoPrecio);

            // Buscar si ya existe un precio para esta moneda
            $precioExistente = ProductoPrecio::where('producto_id', $producto->id)
                ->where('moneda_id', $moneda->id)
                ->first();

            if ($nuevoPrecio !== null && $nuevoPrecio !== '') {
                // Si hay precio en el formulario, actualizar o crear
                if ($precioExistente) {
                    $precioExistente->update([
                        'precio' => $nuevoPrecio,
                        'is_active' => true
                    ]);
                } else {
                    ProductoPrecio::create([
                        'producto_id' => $producto->id,
                        'moneda_id' => $moneda->id,
                        'precio' => $nuevoPrecio,
                        'is_active' => true
                    ]);
                }
            } else {
                // Si no hay precio en el formulario y existe un precio, eliminarlo o desactivarlo
                if ($precioExistente && $moneda->codigo_iso != 'USD') {
                    // Solo eliminar/desactivar si no es USD
                    $precioExistente->delete(); // o update(['is_active' => false])
                }
            }
        }

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

        // Eliminar todas las imágenes asociadas del sistema de archivos
        for ($i = 1; $i <= 5; $i++) {
            $campo = 'imagen_url_' . $i;
            if ($producto->$campo) {
                // Construir la ruta completa del archivo
                $rutaCompleta = public_path($producto->$campo);

                // Verificar si el archivo existe y eliminarlo
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }
        }

        // Eliminar precios asociados (por la relación en la BD)
        if ($producto->precios) {
            $producto->precios()->delete(); // Soft delete si usa SoftDeletes
        }

        // Eliminar stock si existe
        if ($producto->stock) {
            $producto->stock->delete();
        }

        // Eliminar el producto
        $producto->delete();

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
