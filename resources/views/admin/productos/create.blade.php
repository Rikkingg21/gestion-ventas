@extends('layouts.admin.app')

@section('title', 'Nuevo Producto')
@section('page-title', 'Crear Nuevo Producto')

@section('content')
<div class="bg-white rounded-lg shadow-lg max-w-4xl mx-auto">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Información del Producto</h3>
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

    <form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
        @csrf

        <!-- Tipo de Producto -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Producto</label>
            <div class="flex space-x-6">
                <label class="inline-flex items-center">
                    <input type="radio" name="tipo_producto" value="fisico"
                           {{ old('tipo_producto', 'fisico') == 'fisico' ? 'checked' : '' }}
                           class="form-radio h-4 w-4 text-indigo-600" id="tipo_fisico">
                    <span class="ml-2 text-gray-700">Producto Físico</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="tipo_producto" value="digital"
                           {{ old('tipo_producto') == 'digital' ? 'checked' : '' }}
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
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
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
                           value="{{ old('nombre') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('nombre') border-red-500 @enderror"
                           required>
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Múltiples Imágenes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Imágenes del Producto (Máximo 5)
                    </label>
                    <div class="space-y-4" id="imagenes-container">
                        <div class="flex items-center space-x-2">
                            <input type="file"
                                   name="imagenes[]"
                                   id="imagen_1"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                   accept="image/*"
                                   onchange="previewImage(this, 1)">
                            <button type="button" onclick="agregarCampoImagen()" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-plus-circle text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Selecciona hasta 5 imágenes. Formatos: JPG, PNG, GIF, WEBP. Máx 2MB cada una.</p>
                    @error('imagenes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('imagenes.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Previsualización de imágenes -->
                <div id="preview-container" class="grid grid-cols-3 gap-4 mt-4"></div>

                <!-- SKU (solo para físicos) -->
                <div id="campo_sku" class="{{ old('tipo_producto', 'fisico') == 'digital' ? 'hidden' : '' }}">
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-2">
                        SKU / Código <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="sku"
                           id="sku"
                           value="{{ old('sku') }}"
                           placeholder="Ej: PROD-001"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('sku') border-red-500 @enderror">
                    @error('sku')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL Recurso (solo para digitales) -->
                <div id="campo_url" class="{{ old('tipo_producto') == 'digital' ? '' : 'hidden' }}">
                    <label for="url_recurso" class="block text-sm font-medium text-gray-700 mb-2">
                        URL del Recurso <span class="text-red-500">*</span>
                    </label>
                    <input type="url"
                           name="url_recurso"
                           id="url_recurso"
                           value="{{ old('url_recurso') }}"
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
                                   value="{{ old('precioUSD') }}"
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
                                   value="{{ old('precioLocal') }}"
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

                <!-- Descuento -->
                <div class="space-y-4" id="campo_descuento">
                    <div class="flex items-center">
                        <input type="checkbox"
                               name="aplica_descuento"
                               id="aplica_descuento"
                               value="1"
                               {{ old('aplica_descuento') ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="aplica_descuento" class="ml-2 block text-sm text-gray-700">
                            Aplica descuento
                        </label>
                    </div>
                    <div id="campo_porcentaje" class="{{ old('aplica_descuento') ? '' : 'hidden' }}">
                        <label for="porcentaje_descuento" class="block text-sm font-medium text-gray-700 mb-2">
                            Porcentaje de Descuento <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number"
                                   name="porcentaje_descuento"
                                   id="porcentaje_descuento"
                                   value="{{ old('porcentaje_descuento') }}"
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
                <div id="campo_stock" class="space-y-4 {{ old('tipo_producto', 'fisico') == 'digital' ? 'hidden' : '' }}">
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad en Stock <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               name="stock"
                               id="stock"
                               value="{{ old('stock', 0) }}"
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
                               value="{{ old('stock_minimo', 0) }}"
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
                           {{ old('is_active', true) ? 'checked' : '' }}
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
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ old('descripcion') }}</textarea>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.productos.index') }}"
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-save mr-2"></i>
                Guardar Producto
            </button>
        </div>
    </form>
</div>

<script>
    let contadorImagenes = 1;
    const maxImagenes = 5;

    function agregarCampoImagen() {
        const container = document.getElementById('imagenes-container');
        const camposActuales = container.children.length;

        if (camposActuales < maxImagenes) {
            contadorImagenes++;
            const nuevoCampo = document.createElement('div');
            nuevoCampo.className = 'flex items-center space-x-2 mt-2';
            nuevoCampo.innerHTML = `
                <input type="file"
                       name="imagenes[]"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                       accept="image/*"
                       onchange="previewImage(this, ${camposActuales + 1})">
                <button type="button" onclick="eliminarCampoImagen(this)" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-minus-circle text-xl"></i>
                </button>
            `;
            container.appendChild(nuevoCampo);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Límite alcanzado',
                text: 'Solo puedes subir hasta 5 imágenes'
            });
        }
    }

    function eliminarCampoImagen(boton) {
        const campo = boton.closest('.flex');
        const index = Array.from(campo.parentNode.children).indexOf(campo);

        // Eliminar la preview correspondiente si existe
        const previews = document.getElementById('preview-container').children;
        if (previews[index] && previews[index].tagName === 'DIV') {
            previews[index].remove();
        }

        campo.remove();
    }

    function previewImage(input, index) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            const previewContainer = document.getElementById('preview-container');

            reader.onload = function(e) {
                // Limpiar preview existente para este índice
                const previews = previewContainer.children;
                for (let i = 0; i < previews.length; i++) {
                    if (previews[i].getAttribute('data-index') == index) {
                        previews[i].remove();
                        break;
                    }
                }

                // Crear nueva preview
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative';
                previewDiv.setAttribute('data-index', index);
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border shadow-sm">
                    <button type="button" onclick="eliminarPreview(this, ${index})" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                        ×
                    </button>
                `;
                previewContainer.appendChild(previewDiv);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    function eliminarPreview(boton, index) {
        // Eliminar la preview
        boton.parentElement.remove();

        // Buscar y limpiar el input de archivo correspondiente
        const inputs = document.querySelectorAll('input[type="file"][name="imagenes[]"]');
        for (let input of inputs) {
            if (input.value) {
                input.value = '';
                break;
            }
        }
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
                document.getElementById('porcentaje_descuento').value = '';
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
