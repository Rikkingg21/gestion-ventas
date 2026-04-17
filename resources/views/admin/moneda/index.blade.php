@extends('layouts.admin.app')

@section('title', 'Monedas')
@section('page-title', 'Gestión de Monedas')

@section('content')
<div class="bg-white rounded-lg shadow-lg">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-semibold text-gray-800">Listado de Monedas</h3>
            @if ($currentUser->canCreate('moneda'))
            <a href="{{ route('admin.moneda.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Nueva Moneda
            </a>
            @endif
        </div>
    </div>

    <!-- Búsqueda -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('admin.moneda.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Buscar por país, nombre o código ISO..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </button>
                <a href="{{ route('admin.moneda.index') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Monedas -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">País</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Moneda</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código ISO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Símbolo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tasa Cambio USD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($monedas as $moneda)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium">{{ $moneda->pais }}</div>
                        @if($moneda->pais_code)
                            <div class="text-xs text-gray-500">{{ $moneda->pais_code }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $moneda->nombre }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-mono font-semibold bg-gray-100 text-gray-700 rounded">
                            {{ $moneda->codigo_iso }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xl">{{ $moneda->simbolo }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm">
                            1 USD = {{ number_format($moneda->tasa_cambio_usd, 2) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $moneda->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $moneda->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                        @if($moneda->aceptar_pagos)
                            <span class="ml-1 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-credit-card mr-1"></i>Pagos
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                        @if ($currentUser->canUpdate('moneda'))
                        <a href="{{ route('admin.moneda.edit', $moneda->id) }}"
                           class="text-yellow-600 hover:text-yellow-900 p-1 inline-block"
                           title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @if ($currentUser->canDelete('moneda'))
                        <form action="{{ route('admin.moneda.destroy', $moneda->id) }}"
                              method="POST"
                              class="inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    onclick="confirmDelete(this)"
                                    class="text-red-600 hover:text-red-900 p-1"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-coins text-4xl mb-3 text-gray-300"></i>
                        <p>No hay monedas registradas</p>
                        @if ($currentUser->canCreate('moneda'))
                            <a href="{{ route('admin.moneda.create') }}" class="text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                                Crear primera moneda
                            </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $monedas->links() }}
    </div>
</div>

<script>
function confirmDelete(button) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('form').submit();
        }
    });
}
</script>
@endsection
