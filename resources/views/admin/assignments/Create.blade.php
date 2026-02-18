@extends('layouts.admin.app')

@section('title', 'Nueva Asignación')
@section('page-title', 'Asignar Permiso a Administrador')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('admin.assignments.index') }}" class="text-indigo-600 hover:text-indigo-900 mb-2 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a asignaciones
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Asignar Nuevo Permiso</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.assignments.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <!-- Seleccionar Administrador -->
                <div>
                    <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Administrador <span class="text-red-500">*</span>
                    </label>
                    <select id="admin_id" name="admin_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('admin_id') border-red-500 @enderror">
                        <option value="">Seleccione un administrador</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ old('admin_id') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->user->name }} ({{ $admin->user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('admin_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Seleccionar Permiso por Módulo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Permiso <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-4">
                        @foreach($modules as $module)
                            <div class="border rounded-lg p-4">
                                <h3 class="font-medium text-gray-800 mb-2">{{ $module->name }}</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @foreach($module->permissions as $permission)
                                        <label class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded">
                                            <input type="radio"
                                                   name="permission_id"
                                                   value="{{ $permission->id }}"
                                                   {{ old('permission_id') == $permission->id ? 'checked' : '' }}
                                                   class="text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permission_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de Expiración (opcional) -->
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha de Expiración (opcional)
                    </label>
                    <input type="datetime-local"
                           id="expires_at"
                           name="expires_at"
                           value="{{ old('expires_at') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('expires_at') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Dejar vacío para permisos permanentes</p>
                    @error('expires_at')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notas -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notas (opcional)
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('notes') border-red-500 @enderror"
                              placeholder="Motivo de la asignación, observaciones, etc.">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.assignments.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Asignar Permiso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
