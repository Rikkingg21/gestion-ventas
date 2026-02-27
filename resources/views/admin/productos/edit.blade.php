@extends('layouts.admin.app')

@section('title', 'Editar Producto')
@section('page-title', 'Editar Producto')

@section('content')
<div class="bg-white rounded-lg shadow-lg max-w-4xl mx-auto">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Editando: {{ $producto->nombre }}</h3>
    </div>

    @if ($errors->any())
        <div class="mx-6 mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                    <span class="ml-2 text-gray-700">Producto Digital</span>
                </label>
            </div>
        </div>

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
                @php
                    $imagenesExistentes = 0;
                    for ($i = 1; $i <= 5; $i++) {
                        if ($producto->{'imagen_url_' . $i}) $imagenesExistentes++;
                    }
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Imágenes Actuales ({{ $imagenesExistentes }}/5)
                    </label>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        @for ($i = 1; $i <= 5; $i++)
                            @php
                                $campo = 'imagen_url_' . $i;
                            @endphp
                            @if($producto->$campo)
                                <div class="relative group" id="imagen-{{ $i }}">
                                    <img src="{{ $producto->getImageUrl($campo) }}" alt="Imagen {{ $i }}"
                                         class="w-full h-24 object-cover rounded-lg border shadow-sm">
                                    <button type="button"
                                            onclick="eliminarImagenExistente({{ $i }})"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
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
                        Agregar Nuevas Imágenes (Máximo {{ 5 - $imagenesExistentes }} restantes)
                    </label>
                    <div class="space-y-4" id="imagenes-container">
                        <div class="flex items-center space-x-2">
                            <input type="file"
                                   name="imagenes_nuevas[]"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                   accept="image/*"
                                   onchange="previewNuevaImagen(this)">
                            <button type="button" onclick="agregarCampoImagen()" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-plus-circle text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Formatos: JPG, PNG, GIF, WEBP. Máx 2MB cada una.</p>
                </div>

                <!-- Previsualización de nuevas imágenes -->
                <div id="preview-nuevas" class="grid grid-cols-3 gap-4 mt-4"></div>

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
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Precios por Moneda <span class="text-red-500">*</span>
                    </label>

                    @foreach($monedas as $moneda)
                        @php
                            // Buscar el precio existente para esta moneda
                            $precioExistente = $producto->precios->where('moneda_id', $moneda->id)->first();
                            $valorPrecio = old('precio_' . $moneda->codigo_iso, $precioExistente ? $precioExistente->precio : '');
                        @endphp
                        <div>
                            <label for="precio_{{ $moneda->codigo_iso }}" class="block text-sm text-gray-600 mb-1">
                                Precio en {{ $moneda->nombre }} ({{ $moneda->codigo_iso }})
                                @if($moneda->codigo_iso == 'USD')
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">{{ $moneda->simbolo }}</span>
                                <input type="number"
                                    name="precio_{{ $moneda->codigo_iso }}"
                                    id="precio_{{ $moneda->codigo_iso }}"
                                    value="{{ $valorPrecio }}"
                                    step="0.01"
                                    min="0"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @if($moneda->codigo_iso == 'USD') required @endif"
                                    @if($moneda->codigo_iso == 'USD') required @endif>
                            </div>
                            @error('precio_' . $moneda->codigo_iso)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                    <p class="text-xs text-gray-500">El precio en USD es obligatorio. Los demás precios son opcionales.</p>
                </div>

                <!-- Descuento -->
                <div class="space-y-4" id="campo_descuento">
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
                    <div id="campo_porcentaje" class="{{ old('aplica_descuento', $producto->aplica_descuento) ? '' : 'hidden' }}">
                        <label for="porcentaje_descuento" class="block text-sm font-medium text-gray-700 mb-2">
                            Porcentaje de Descuento
                        </label>
                        <div class="relative">
                            <input type="number"
                                   name="porcentaje_descuento"
                                   id="porcentaje_descuento"
                                   value="{{ old('porcentaje_descuento', $producto->porcentaje_descuento) }}"
                                   min="0"
                                   max="100"
                                   step="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('porcentaje_descuento') border-red-500 @enderror">
                            <span class="absolute right-3 top-2 text-gray-500">%</span>
                        </div>
                        @error('porcentaje_descuento')
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

                <!-- Estado -->
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

        <!-- Botones -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.productos.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
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
        const imagenesVisibles = document.querySelectorAll('[id^="imagen-"]').length;
        const totalActual = imagenesVisibles + camposActuales;

        if (totalActual < maxImagenes) {
            contadorImagenes++;
            const nuevoCampo = document.createElement('div');
            nuevoCampo.className = 'flex items-center space-x-2 mt-2';
            nuevoCampo.innerHTML = `
                <input type="file"
                       name="imagenes_nuevas[]"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                       accept="image/*"
                       onchange="previewNuevaImagen(this)">
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
        const campo = boton.closest('.flex');
        campo.remove();
    }

    function previewNuevaImagen(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            const previewContainer = document.getElementById('preview-nuevas');

            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border shadow-sm">
                `;
                previewContainer.appendChild(previewDiv);
            }

            reader.readAsDataURL(input.files[0]);
        }
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
                // Agregar posición a la lista de imágenes a eliminar
                if (!imagenesEliminar.includes(posicion)) {
                    imagenesEliminar.push(posicion);
                }

                // Actualizar el campo oculto con las posiciones a eliminar
                document.getElementById('imagenes_eliminar').value = JSON.stringify(imagenesEliminar);

                // Ocultar la imagen visualmente
                const imagenDiv = document.getElementById('imagen-' + posicion);
                if (imagenDiv) {
                    imagenDiv.remove();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Marcada para eliminar',
                    text: 'La imagen se eliminará al guardar los cambios',
                    timer: 2000,
                    showConfirmButton: false
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
        const aplicaDescuento = document.getElementById('aplica_descuento');
        const campoPorcentaje = document.getElementById('campo_porcentaje');

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

        function toggleDescuento() {
            if (aplicaDescuento.checked) {
                campoPorcentaje.classList.remove('hidden');
                document.getElementById('porcentaje_descuento').required = true;
            } else {
                campoPorcentaje.classList.add('hidden');
                document.getElementById('porcentaje_descuento').required = false;
            }
        }

        tipoFisico.addEventListener('change', toggleCampos);
        tipoDigital.addEventListener('change', toggleCampos);
        aplicaDescuento.addEventListener('change', toggleDescuento);

        // Inicializar estados
        toggleCampos();
        toggleDescuento();
    });
</script>
@endsection
