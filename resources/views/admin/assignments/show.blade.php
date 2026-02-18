@extends('layouts.admin.app')

@section('title', 'Permisos de Administrador')
@section('page-title', 'Permisos asignados')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('admin.assignments.index') }}" class="text-indigo-600 hover:text-indigo-900 mb-2 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a asignaciones
        </a>
    </div>

    <!-- Info del Administrador -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                <span class="text-indigo-600 font-bold text-2xl">
                    {{ substr($admin->user->name, 0, 1) }}
                </span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $admin->user->name }}</h1>
                <p class="text-gray-600">{{ $admin->user->email }}</p>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $admin->nivel == 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $admin->nivel ?? 'Administrador' }}
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ $admin->permissions->count() }} permisos asignados
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Asignación Masiva -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Asignación Masiva de Permisos</h2>

        <form action="{{ route('admin.assignments.bulk') }}" method="POST">
            @csrf
            <input type="hidden" name="admin_id" value="{{ $admin->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Selección de Permisos por Módulo -->
                <div class="md:col-span-2">
                    <div class="space-y-4">
                        @foreach($modules as $module)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="font-medium text-gray-800">{{ $module->name }}</h3>
                                    <button type="button"
                                            onclick="toggleModule({{ $module->id }})"
                                            class="text-sm text-indigo-600 hover:text-indigo-900">
                                        Seleccionar todos
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    @foreach($module->permissions as $permission)
                                        @php
                                            $hasPermission = $admin->permissions->contains('id', $permission->id);
                                        @endphp
                                        <label class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded {{ $hasPermission ? 'bg-green-50' : '' }}">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   class="module-{{ $module->id }} text-indigo-600 focus:ring-indigo-500 rounded"
                                                   {{ $hasPermission ? 'checked disabled' : '' }}>
                                            <span class="text-sm {{ $hasPermission ? 'text-green-600' : '' }}">
                                                {{ $permission->name }}
                                                @if($hasPermission)
                                                    <span class="text-xs text-green-500">(ya asignado)</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Fecha de Expiración -->
                <div>
                    <label for="bulk_expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha de Expiración (opcional)
                    </label>
                    <input type="datetime-local"
                           id="bulk_expires_at"
                           name="expires_at"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Notas -->
                <div>
                    <label for="bulk_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notas (opcional)
                    </label>
                    <input type="text"
                           id="bulk_notes"
                           name="notes"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="Motivo de la asignación">
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Asignar Permisos Seleccionados
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Permisos Actuales -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Permisos Asignados Actualmente</h2>
        </div>

        <div class="p-6">
            @if($admin->permissions->count() > 0)
                <div class="space-y-4">
                    @foreach($admin->permissions->groupBy('module.name') as $moduleName => $permissions)
                        <div>
                            <h3 class="font-medium text-gray-700 mb-2">{{ $moduleName }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($permissions as $permission)
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $permission->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                Asignado por: {{ $permission->pivot->assignedBy->user->name ?? 'Sistema' }}
                                                @if($permission->pivot->expires_at)
                                                    · Expira: {{ \Carbon\Carbon::parse($permission->pivot->expires_at)->format('d/m/Y H:i') }}
                                                @endif
                                            </p>
                                            @if($permission->pivot->notes)
                                                <p class="text-xs text-gray-500 mt-1">📝 {{ $permission->pivot->notes }}</p>
                                            @endif
                                        </div>
                                        <form action=""
                                              method="POST"
                                              onsubmit="return confirm('¿Remover este permiso?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No hay permisos asignados a este administrador</p>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleModule(moduleId) {
        const checkboxes = document.querySelectorAll(`.module-${moduleId}`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked || cb.disabled);

        checkboxes.forEach(cb => {
            if (!cb.disabled) {
                cb.checked = !allChecked;
            }
        });
    }
</script>
@endsection
