@extends('layouts.admin.app')

@section('title', 'Asignaciones de Permisos')
@section('page-title', 'Gestión de Permisos por Administrador')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Cabecera -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Asignaciones de Permisos</h1>
        <a href="{{ route('admin.assignments.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Asignación
        </a>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Lista de Administradores -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($admins as $admin)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Cabecera del Admin -->
                <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-bold text-xl">
                                {{ substr($admin->user->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ $admin->user->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $admin->user->email }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $admin->nivel == 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $admin->nivel ?? 'Administrador' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.assignments.show', $admin) }}"
                       class="text-indigo-600 hover:text-indigo-900 font-medium">
                        Ver Detalles
                    </a>
                </div>

                <!-- Permisos Asignados -->
                <div class="p-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Permisos Activos</h4>

                    @if($admin->permissions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($admin->permissions as $permission)
                                <div class="flex items-start space-x-2 p-2 bg-gray-50 rounded-lg">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $permission->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $permission->module->name }}</p>
                                        @if($permission->pivot->expires_at)
                                            <p class="text-xs text-yellow-600">
                                                Expira: {{ $permission->pivot->expires_at->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No tiene permisos asignados</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay administradores</h3>
                <p class="text-gray-500">Primero debes crear administradores para asignar permisos</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
