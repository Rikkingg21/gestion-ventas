@extends('layouts.admin.app')

@section('title', 'Editar Producto')
@section('page-title', 'Editar Producto')

@section('content')
<div class="bg-white rounded-lg shadow-lg max-w-4xl mx-auto">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Editando: {{ $producto->nombre }}</h3>
    </div>

    <form action="{{ route('admin.productos.update', $producto->id) }}" method="POST" enctype="multipart/form-data" class="p-6">
        @csrf
        @method('PUT')

        <!-- Tipo de Producto -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Producto</label>
            <div class="flex space-x-6">
                <label class="inline-flex items-center">
                    <input type="radio" name="tipo_producto" value="fisico"
                           {{ old('tipo_producto', $producto->tipo_producto) == 'fisico' ? 'checked' : '' }}
                           class="form-radio h-4 w-4 text-indigo-600" id="tipo_fisico">
                    <span class="ml-2 text-gray-700">Producto Físico</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="tipo_producto" value="digital"
                           {{ old('tipo_producto', $producto->tipo_producto) == 'digital' ? 'checked' : '' }}
                           class="form-radio h-4 w-4 text-indigo-600" id="tipo_digital">
                    <span class="ml-2 text-gray-700">Producto Digital (Curso)</span>
                </label>
            </div>
        </div>

        <!-- Mensajes de error -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="list-disc pl-5 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Columna Izquierda -->
            <div class="space-y-6">
                <!-- Categoría -->
                <div>
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <select name="categoria_id"
                            id="categoria_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('categoria_id') border-red-500 @enderror"
                            required>
                        <option value="">Seleccione una categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nombre -->
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Producto <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="nombre"
                           id="nombre"
                           value="{{ old('nombre', $producto->nombre) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('nombre') border-red-500 @enderror"
                           required>
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Imágenes actuales -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Imágenes Actuales
                    </label>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        @for ($i = 1; $i <= 5; $i++)
                            @php
                                $campo = 'imagen_url_' . $i;
                            @endphp
                            @if($producto->$campo)
                                <div class="relative group" id="imagen-{{ $i }}">
                                    <img src="{{ asset($producto->$campo) }}" alt="Imagen {{ $i }}"
                                         class="w-full h-24 object-cover rounded-lg border">
                                    <button type="button"
                                            onclick="eliminarImagenExistente({{ $i }})"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                    <input type="hidden" name="imagenes_existentes[{{ $i }}]" value="{{ $producto->$campo }}" class="imagen-existente">
                                </div>
                            @endif
                        @endfor
                    </div>
                    <input type="hidden" name="imagenes_eliminar" id="imagenes_eliminar" value="">
                </div>

                <!-- Nuevas imágenes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Agregar Nuevas Imágenes (Máximo {{ 5 - count($imagenesExistentes) }} restantes)
                    </label>
                    <div class="space-y-4" id="imagenes-container">
                        <div class="flex items-center space-x-2">
                            <input type="file"
                                   name="imagenes_nuevas[]"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                   accept="image/*">
                            <button type="button" onclick="agregarCampoImagen()" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-plus-circle text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Formatos: JPG, PNG, GIF, WEBP. Máx 2MB cada una.</p>
                </div>

                <!-- SKU (solo para físicos) -->
                <div id="campo_sku" class="{{ $producto->tipo_producto == 'digital' ? 'hidden' : '' }}">
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">
                        SKU / Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="sku"
                           id="sku"
                           value="{{ old('sku', $producto->sku) }}"
                           placeholder="Ej: PROD-001"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('sku') border-red-500 @enderror">
                    @error('sku')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL Recurso (solo para digitales) -->
                <div id="campo_url" class="{{ $producto->tipo_producto == 'digital' ? '' : 'hidden' }}">
                    <label for="url_recurso" class="block text-sm font-medium text-gray-700 mb-2">
                        URL del Recurso <span class="text-red-500">*</span>
                    </label>
                    <input type="url"
                           name="url_recurso"
                           id="url_recurso"
                           value="{{ old('url_recurso', $producto->url_recurso) }}"
                           placeholder="https://drive.google.com/..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('url_recurso') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Enlace a Google Drive, Dropbox, etc.</p>
                    @error('url_recurso')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="space-y-6">
                <!-- Precios -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="precioUSD" class="block text-sm font-medium text-gray-700 mb-2">
                            Precio USD <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number"
                                   name="precioUSD"
                                   id="precioUSD"
                                   value="{{ old('precioUSD', $producto->precioUSD) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('precioUSD') border-red-500 @enderror"
                                   required>
                        </div>
                        @error('precioUSD')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="precioLocal" class="block text-sm font-medium text-gray-700 mb-2">
                            Precio Local <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">S/</span>
                            <input type="number"
                                   name="precioLocal"
                                   id="precioLocal"
                                   value="{{ old('precioLocal', $producto->precioLocal) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('precioLocal') border-red-500 @enderror"
                                   required>
                        </div>
                        @error('precioLocal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Stock (solo para físicos) -->
                <div id="campo_stock" class="space-y-4 {{ $producto->tipo_producto == 'digital' ? 'hidden' : '' }}">
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad en Stock <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="stock"
                               id="stock"
                               value="{{ old('stock', optional($producto->stock)->cantidad ?? 0) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('stock') border-red-500 @enderror">
                        @error('stock')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-2">
                            Stock Mínimo (opcional)
                        </label>
                        <input type="number"
                               name="stock_minimo"
                               id="stock_minimo"
                               value="{{ old('stock_minimo', optional($producto->stock)->stock_minimo ?? 0) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Notificación cuando el stock esté por debajo de este número</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descripción -->
        <div class="mt-6">
            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                Descripción del Producto
            </label>
            <textarea name="descripcion"
                      id="descripcion"
                      rows="5"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ old('descripcion', $producto->descripcion) }}</textarea>
        </div>

        <!-- Opciones adicionales -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center">
                <input type="checkbox"
                       name="aplica_descuento"
                       id="aplica_descuento"
                       value="1"
                       {{ old('aplica_descuento', $producto->aplica_descuento) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="aplica_descuento" class="ml-2 block text-sm text-gray-700">
                    Aplica descuento
                </label>
            </div>
            <div class="flex items-center">
                <input type="checkbox"
                       name="is_active"
                       id="is_active"
                       value="1"
                       {{ old('is_active', $producto->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    Producto activo
                </label>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.productos.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-save mr-2"></i>
                Actualizar Producto
            </button>
        </div>
    </form>
</div>

<script>
    let contadorImagenes = 1;
    const maxImagenes = 5;
    let imagenesEliminar = [];

    function agregarCampoImagen() {
        const container = document.getElementById('imagenes-container');
        const camposActuales = container.children.length;

        // Contar imágenes existentes visibles (no eliminadas)
        const imagenesVisibles = document.querySelectorAll('[id^="imagen-"]').length;
        const totalActual = imagenesVisibles + camposActuales;

        if (totalActual < maxImagenes) {
            const nuevoCampo = document.createElement('div');
            nuevoCampo.className = 'flex items-center space-x-2 mt-2';
            nuevoCampo.innerHTML = `
                <input type="file"
                    name="imagenes_nuevas[]"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    accept="image/*">
                <button type="button" onclick="eliminarCampoImagen(this)" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-minus-circle text-xl"></i>
                </button>
            `;
            container.appendChild(nuevoCampo);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Límite alcanzado',
                text: 'Solo puedes tener hasta 5 imágenes en total'
            });
        }
    }

    function eliminarCampoImagen(boton) {
        boton.closest('.flex').remove();
    }

    function eliminarImagenExistente(posicion) {
        Swal.fire({
            title: '¿Eliminar imagen?',
            text: "Esta acción no se puede revertir",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar petición AJAX para eliminar la imagen
                fetch('{{ route("admin.productos.eliminar-imagen", $producto->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        posicion: posicion
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Agregar posición a la lista de imágenes a eliminar
                        if (!imagenesEliminar.includes(posicion)) {
                            imagenesEliminar.push(posicion);
                        }

                        // Actualizar el campo oculto con el JSON
                        document.getElementById('imagenes_eliminar').value = JSON.stringify(imagenesEliminar);

                        // Ocultar la imagen visualmente
                        const imagenDiv = document.getElementById('imagen-' + posicion);
                        if (imagenDiv) {
                            imagenDiv.remove(); // Eliminar completamente el div de la imagen
                        }

                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminada!',
                            text: 'La imagen ha sido eliminada correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo eliminar la imagen'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar la imagen'
                    });
                });
            }
        });
    }

    // Toggle campos según tipo de producto
    document.addEventListener('DOMContentLoaded', function() {
        const tipoFisico = document.getElementById('tipo_fisico');
        const tipoDigital = document.getElementById('tipo_digital');
        const campoSku = document.getElementById('campo_sku');
        const campoStock = document.getElementById('campo_stock');
        const campoUrl = document.getElementById('campo_url');

        function toggleCampos() {
            if (tipoDigital.checked) {
                campoSku.classList.add('hidden');
                campoStock.classList.add('hidden');
                campoUrl.classList.remove('hidden');

                // Quitar required de campos físicos
                if (document.getElementById('sku')) document.getElementById('sku').required = false;
                if (document.getElementById('stock')) document.getElementById('stock').required = false;
                // Agregar required a URL
                if (document.getElementById('url_recurso')) document.getElementById('url_recurso').required = true;
            } else {
                campoSku.classList.remove('hidden');
                campoStock.classList.remove('hidden');
                campoUrl.classList.add('hidden');

                // Agregar required a campos físicos
                if (document.getElementById('sku')) document.getElementById('sku').required = true;
                if (document.getElementById('stock')) document.getElementById('stock').required = true;
                // Quitar required de URL
                if (document.getElementById('url_recurso')) document.getElementById('url_recurso').required = false;
            }
        }

        tipoFisico.addEventListener('change', toggleCampos);
        tipoDigital.addEventListener('change', toggleCampos);
        toggleCampos();
    });
</script>
@endsection
