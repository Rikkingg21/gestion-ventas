@extends('layouts.admin.app')

@section('title', 'Módulos')
@section('page-title', 'Gestión de Módulos')

@section('content')
@php
    $currentUser = Auth::guard('admin')->user();
@endphp

<div class="container mx-auto px-4 py-6">
    <!-- Cabecera -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Módulos</h1>

        {{-- Botón crear - Solo si tiene permiso --}}
        @if($currentUser->canCreate('modules'))
        <a href="{{ route('admin.modules.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Módulo
        </a>
        @endif
    </div>

    <!-- Mensajes de alerta (se mantienen igual) -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Filtros (se mantienen igual) -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" id="searchModules" placeholder="Buscar módulos por nombre o slug..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <select id="filterStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Lista de módulos -->
    <div class="space-y-4" id="modulesList">
        @forelse($modules as $module)
            <div class="module-card bg-white rounded-lg shadow-md overflow-hidden border border-gray-200"
                 data-module-id="{{ $module->id }}"
                 data-status="{{ $module->is_active ? 'active' : 'inactive' }}">

                <!-- Cabecera del módulo -->
                <div class="module-header flex justify-between items-center p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-150"
                     onclick="toggleModule({{ $module->id }})">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl text-indigo-600">
                            <i class="fas {{ $module->icon ?: 'fa-cube' }}"></i>
                        </span>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">{{ $module->name }}</h3>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-sm text-gray-500">{{ $module->slug }}</span>
                                @if($module->parent_id)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                        </svg>
                                        Submódulo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $module->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span class="w-2 h-2 rounded-full mr-2 {{ $module->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $module->is_active ? 'Activo' : 'Inactivo' }}
                        </span>

                        {{-- Botones de acción --}}
                        <div class="flex items-center space-x-2" onclick="event.stopPropagation()">
                            {{-- Editar - Solo si tiene permiso --}}
                            @if($currentUser->canUpdate('modules'))
                            <a href="{{ route('admin.modules.edit', $module) }}"
                               class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-150"
                               title="Editar módulo">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endif

                            {{-- Eliminar - Solo si tiene permiso --}}
                            @if($currentUser->canDelete('modules'))
                            <form action="{{ route('admin.modules.destroy', $module) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('¿Estás seguro de eliminar el módulo {{ $module->name }}? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors duration-150"
                                        title="Eliminar módulo"
                                        {{ $module->children->count() > 0 || $module->permisos->count() > 0 ? 'disabled' : '' }}>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contenido expandible del módulo (se mantiene igual) -->
                <div class="module-content hidden border-t border-gray-200 bg-gray-50" id="module-{{ $module->id }}">
                    <!-- ... contenido existente ... -->
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay módulos creados</h3>
                <p class="text-gray-500 mb-4">Comienza creando tu primer módulo para organizar los permisos del sistema</p>

                {{-- Botón crear solo si tiene permiso --}}
                @if($currentUser->canCreate('modules'))
                <a href="{{ route('admin.modules.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Crear Primer Módulo
                </a>
                @endif
            </div>
        @endforelse
    </div>
</div>

<script>
    // Funciones existentes se mantienen igual
    function toggleModule(moduleId) {
        const moduleContent = document.getElementById(`module-${moduleId}`);
        const allContents = document.querySelectorAll('[id^="module-"]');
        const moduleCard = moduleContent.closest('.module-card');

        allContents.forEach(content => {
            if (content.id !== `module-${moduleId}` && !content.classList.contains('hidden')) {
                content.classList.add('hidden');
                const card = content.closest('.module-card');
                card.classList.remove('border-indigo-500', 'border-2');
            }
        });

        if (moduleContent.classList.contains('hidden')) {
            moduleContent.classList.remove('hidden');
            moduleCard.classList.add('border-indigo-500', 'border-2');
        } else {
            moduleContent.classList.add('hidden');
            moduleCard.classList.remove('border-indigo-500', 'border-2');
        }
    }

    document.getElementById('searchModules').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const modules = document.querySelectorAll('.module-card');

        modules.forEach(module => {
            const moduleName = module.querySelector('h3').textContent.toLowerCase();
            const moduleSlug = module.querySelector('.text-sm.text-gray-500').textContent.toLowerCase();

            if (moduleName.includes(searchTerm) || moduleSlug.includes(searchTerm)) {
                module.style.display = 'block';
            } else {
                module.style.display = 'none';
            }
        });
    });

    document.getElementById('filterStatus').addEventListener('change', function() {
        const filter = this.value;
        const modules = document.querySelectorAll('.module-card');

        modules.forEach(module => {
            const status = module.dataset.status;

            if (filter === '' || filter === status) {
                module.style.display = 'block';
            } else {
                module.style.display = 'none';
            }
        });
    });
</script>
@endsection
