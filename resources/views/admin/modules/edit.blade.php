@extends('layouts.admin.app')

@section('title', 'Editar Módulo')
@section('page-title', 'Editar Módulo')

@section('content')

<div class="container mx-auto px-4 py-6">
    <!-- Cabecera -->
    <div class="mb-6">
        <a href="{{ route('admin.modules.index') }}" class="text-indigo-600 hover:text-indigo-900 mb-2 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a módulos
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Editar Módulo: {{ $module->name }}</h1>
        <p class="text-gray-600 mt-1">Modifique los campos que desea actualizar</p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.modules.update', $module) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="col-span-2 md:col-span-1">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Módulo <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $module->name) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Ej: Usuarios, Productos, Ventas...">
                    @error('name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="col-span-2 md:col-span-1">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="slug"
                           name="slug"
                           value="{{ old('slug', $module->slug) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('slug') border-red-500 @enderror"
                           placeholder="Ej: usuarios, productos, ventas">
                    <p class="mt-1 text-xs text-gray-500">Identificador único (solo letras minúsculas y guiones bajos)</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icono -->
                <div class="col-span-2 md:col-span-1">
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Icono (Font Awesome)
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500">
                            <i class="fas {{ old('icon', $module->icon ?: 'fa-cube') }}" id="iconPreview"></i>
                        </span>
                        <input type="text"
                               id="icon"
                               name="icon"
                               value="{{ old('icon', $module->icon ?: 'fa-cube') }}"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('icon') border-red-500 @enderror"
                               placeholder="fa-users, fa-box, fa-shopping-cart">
                    </div>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ruta -->
                <div class="col-span-2 md:col-span-1">
                    <label for="route" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de Ruta
                    </label>
                    <input type="text"
                           id="route"
                           name="route"
                           value="{{ old('route', $module->route) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('route') border-red-500 @enderror"
                           placeholder="admin.modules.index">
                    @error('route')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Módulo Padre -->
                <div class="col-span-2 md:col-span-1">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Módulo Padre
                    </label>
                    <select id="parent_id"
                            name="parent_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('parent_id') border-red-500 @enderror">
                        <option value="">-- Ninguno (Módulo Principal) --</option>
                        @foreach($parentModules as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $module->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Seleccione si este módulo depende de otro</p>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Orden -->
                <div class="col-span-2 md:col-span-1">
                    <label for="order_position" class="block text-sm font-medium text-gray-700 mb-2">
                        Posición de Orden
                    </label>
                    <input type="number"
                           id="order_position"
                           name="order_position"
                           value="{{ old('order_position', $module->order_position) }}"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('order_position') border-red-500 @enderror">
                    @error('order_position')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('description') border-red-500 @enderror"
                              placeholder="Descripción del propósito del módulo...">{{ old('description', $module->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div class="col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $module->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Módulo activo</span>
                    </label>
                </div>
            </div>

            <!-- Información adicional sobre submódulos -->
            @if($module->children->count() > 0)
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-yellow-700">
                        <span class="font-medium">Información:</span> Este módulo tiene {{ $module->children->count() }} submódulo(s) asociados.
                        Los cambios que realice afectarán también a sus submódulos.
                    </p>
                </div>
            </div>
            @endif

            <!-- Botones de acción -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.modules.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition duration-150 ease-in-out flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Actualizar Módulo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Vista previa del icono
    document.getElementById('icon').addEventListener('input', function() {
        const iconPreview = document.getElementById('iconPreview');
        const iconClass = this.value || 'fa-cube';
        iconPreview.className = 'fas ' + iconClass;
    });
</script>
@endsection
