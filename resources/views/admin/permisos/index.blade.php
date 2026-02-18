@extends('layouts.admin.app')

@section('title', 'Permisos')
@section('page-title', 'Gestión de Permisos')

@section('content')
@php
    $currentUser = Auth::guard('admin')->user();
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header con estadísticas rápidas --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestión de Permisos</h1>
                    <p class="mt-1 text-sm text-gray-600">Administra los permisos de administradores y personal staff</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Módulo 3
                    </span>
                </div>
            </div>
        </div>

        {{-- Cards de resumen --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-lg p-3">
                        <i class="fas fa-user-tie text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Administradores</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $admins->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-500 rounded-lg p-3">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Staff</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $staffs->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-amber-500 rounded-lg p-3">
                        <i class="fas fa-cubes text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Módulos</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $modules->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs personalizados con Tailwind --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" aria-label="Tabs">
                    <button onclick="switchTab('admin')"
                            class="tab-button active group inline-flex items-center px-6 py-4 border-b-2 border-indigo-500 text-sm font-medium text-indigo-600 bg-indigo-50"
                            id="admin-tab">
                        <i class="fas fa-user-tie mr-2 text-indigo-500"></i>
                        Administradores
                        <span class="ml-3 bg-indigo-100 text-indigo-600 py-0.5 px-2 rounded-full text-xs">
                            {{ $admins->count() }}
                        </span>
                    </button>
                    <button onclick="switchTab('staff')"
                            class="tab-button group inline-flex items-center px-6 py-4 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            id="staff-tab">
                        <i class="fas fa-users mr-2 text-gray-400 group-hover:text-gray-500"></i>
                        Staff
                        <span class="ml-3 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                            {{ $staffs->count() }}
                        </span>
                    </button>
                </nav>
            </div>

            <div class="p-6">
                {{-- Botón de guardar flotante - Solo si tiene permiso de actualizar --}}
                @if($currentUser->canUpdate('permisos'))
                    <div id="bulkAssignBtn" class="fixed bottom-6 right-6 z-50 hidden">
                        <button type="button"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition hover:scale-105">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                @endif

                {{-- Panel de Administradores --}}
                <div id="admin-panel" class="tab-panel">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">
                                        Usuario
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nivel
                                    </th>
                                    @foreach($modules as $module)
                                        <th scope="col" colspan="{{ $permisos->count() }}" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">
                                            <div class="flex items-center justify-center space-x-1">
                                                @if($module->icon)
                                                    <i class="fas fa-{{ $module->icon }}"></i>
                                                @endif
                                                <span>{{ $module->name }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr class="bg-gray-50">
                                    <th scope="col" colspan="2" class="px-6 py-2"></th>
                                    @foreach($modules as $module)
                                        @foreach($permisos as $permiso)
                                            <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <span class="block text-xs">{{ $permiso->nombre }}</span>
                                            </th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($admins as $admin)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white hover:bg-gray-50">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                                        {{ strtoupper(substr($admin->user->nombres, 0, 1)) }}{{ strtoupper(substr($admin->user->apellido_paterno, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $admin->user->nombreCompleto }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $admin->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($admin->nivel === 'super_admin')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-crown mr-1"></i> Super Admin
                                                </span>
                                                <div class="text-xs text-green-600 mt-1">
                                                    <i class="fas fa-check-circle"></i> Todos los permisos
                                                </div>

                                            @elseif($admin->nivel === 'admin')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    <i class="fas fa-user-tie mr-1"></i> Admin
                                                </span>

                                            @elseif($admin->nivel === 'soporte')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-bolt mr-1"></i> Soporte
                                                </span>

                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <i class="fas fa-question-circle mr-1"></i> No tienes grado
                                                </span>
                                            @endif
                                        </td>

                                        @foreach($modules as $module)
                                            @foreach($permisos as $permiso)
                                                <td class="px-2 py-4 text-center">
                                                    @if($admin->nivel === 'super_admin')
                                                        <div class="flex justify-center">
                                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-green-100">
                                                                <i class="fas fa-check text-green-600 text-xs"></i>
                                                            </span>
                                                        </div>
                                                    @else
                                                        @if($currentUser->canUpdate('permisos'))
                                                            <label class="inline-flex items-center cursor-pointer">
                                                                <input type="checkbox"
                                                                       class="permission-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                       id="admin_{{ $admin->id }}_{{ $module->id }}_{{ $permiso->id }}"
                                                                       data-user-type="admin"
                                                                       data-user-id="{{ $admin->id }}"
                                                                       data-modulo-id="{{ $module->id }}"
                                                                       data-permiso-id="{{ $permiso->id }}"
                                                                       {{ $admin->permisos->contains(function($value) use ($module, $permiso) {
                                                                            return $value->module_id == $module->id && $value->permiso_id == $permiso->id;
                                                                        }) ? 'checked' : '' }}>
                                                            </label>
                                                        @else
                                                            @if($admin->permisos->contains(function($value) use ($module, $permiso) {
                                                                return $value->module_id == $module->id && $value->permiso_id == $permiso->id;
                                                            }))
                                                                <div class="flex justify-center">
                                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-100">
                                                                        <i class="fas fa-check text-indigo-600 text-xs"></i>
                                                                    </span>
                                                                </div>
                                                            @else
                                                                <div class="flex justify-center">
                                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-100">
                                                                        <i class="fas fa-times text-gray-400 text-xs"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 2 + ($modules->count() * $permisos->count()) }}" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="bg-gray-100 rounded-full p-3 mb-3">
                                                    <i class="fas fa-user-tie text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="text-gray-500 font-medium">No hay administradores registrados</p>
                                                <p class="text-sm text-gray-400">Los administradores aparecerán aquí cuando sean creados</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Panel de Staff --}}
                <div id="staff-panel" class="tab-panel hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">
                                        Usuario
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cargo / Área
                                    </th>
                                    @foreach($modules as $module)
                                        <th scope="col" colspan="{{ $permisos->count() }}" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">
                                            <div class="flex items-center justify-center space-x-1">
                                                @if($module->icon)
                                                    <i class="fas fa-{{ $module->icon }}"></i>
                                                @endif
                                                <span>{{ $module->name }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr class="bg-gray-50">
                                    <th scope="col" colspan="2" class="px-6 py-2"></th>
                                    @foreach($modules as $module)
                                        @foreach($permisos as $permiso)
                                            <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <span class="block text-xs">{{ $permiso->nombre }}</span>
                                            </th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($staffs as $staff)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white hover:bg-gray-50">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 flex items-center justify-center text-white font-semibold">
                                                        {{ strtoupper(substr($staff->user->nombres, 0, 1)) }}{{ strtoupper(substr($staff->user->apellido_paterno, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $staff->user->nombreCompleto }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $staff->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $staff->cargo }}</div>
                                            <div class="text-sm text-gray-500">{{ $staff->area }}</div>
                                        </td>

                                        @foreach($modules as $module)
                                            @foreach($permisos as $permiso)
                                                <td class="px-2 py-4 text-center">
                                                    @if($currentUser->canUpdate('permisos'))
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="checkbox"
                                                                   class="permission-checkbox rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"
                                                                   id="staff_{{ $staff->id }}_{{ $module->id }}_{{ $permiso->id }}"
                                                                   data-user-type="staff"
                                                                   data-user-id="{{ $staff->id }}"
                                                                   data-modulo-id="{{ $module->id }}"
                                                                   data-permiso-id="{{ $permiso->id }}"
                                                                   {{ $staff->permisos->contains(function($value) use ($module, $permiso) {
                                                                        return $value->module_id == $module->id && $value->permiso_id == $permiso->id;
                                                                    }) ? 'checked' : '' }}>
                                                        </label>
                                                    @else
                                                        @if($staff->permisos->contains(function($value) use ($module, $permiso) {
                                                            return $value->module_id == $module->id && $value->permiso_id == $permiso->id;
                                                        }))
                                                            <div class="flex justify-center">
                                                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-emerald-100">
                                                                    <i class="fas fa-check text-emerald-600 text-xs"></i>
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="flex justify-center">
                                                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-100">
                                                                    <i class="fas fa-times text-gray-400 text-xs"></i>
                                                                </span>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 2 + ($modules->count() * $permisos->count()) }}" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="bg-gray-100 rounded-full p-3 mb-3">
                                                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="text-gray-500 font-medium">No hay staff registrado</p>
                                                <p class="text-sm text-gray-400">El personal aparecerá aquí cuando sea creado</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación con Tailwind --}}
<div id="permisosModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-shield-alt text-indigo-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirmar cambios
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                ¿Estás seguro de que deseas guardar los cambios en los permisos?
                            </p>
                        </div>
                        <div class="mt-3 bg-blue-50 rounded-md p-3">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-blue-700">
                                        Se actualizarán los permisos para el usuario seleccionado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmSave" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <i class="fas fa-save mr-2"></i>
                    Guardar cambios
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    // Actualizar botones
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-indigo-500', 'text-indigo-600', 'bg-indigo-50');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    // Actualizar paneles
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });

    if (tab === 'admin') {
        document.getElementById('admin-tab').classList.add('active', 'border-indigo-500', 'text-indigo-600', 'bg-indigo-50');
        document.getElementById('admin-tab').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('admin-panel').classList.remove('hidden');
    } else {
        document.getElementById('staff-tab').classList.add('active', 'border-indigo-500', 'text-indigo-600', 'bg-indigo-50');
        document.getElementById('staff-tab').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('staff-panel').classList.remove('hidden');
    }

    // Resetear estado
    currentUserType = null;
    currentUserId = null;
    document.getElementById('bulkAssignBtn').classList.add('hidden');
}

// Variables para el manejo de permisos
let currentUserType = null;
let currentUserId = null;
let permisosSeleccionados = [];

// Detectar cambios en checkboxes - Solo si tiene permiso de actualizar
@if($currentUser->canUpdate('permisos'))
document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        let userType = this.dataset.userType;
        let userId = this.dataset.userId;

        if (currentUserType !== userType || currentUserId !== userId) {
            currentUserType = userType;
            currentUserId = userId;
            document.getElementById('bulkAssignBtn').classList.remove('hidden');
        }

        actualizarPermisosSeleccionados(userType, userId);
    });
});
@endif

function actualizarPermisosSeleccionados(userType, userId) {
    permisosSeleccionados = [];

    document.querySelectorAll(`.permission-checkbox[data-user-type="${userType}"][data-user-id="${userId}"]:checked`).forEach(checkbox => {
        permisosSeleccionados.push({
            module_id: parseInt(checkbox.dataset.moduloId),
            permiso_id: parseInt(checkbox.dataset.permisoId)
        });
    });
}

// Botón de guardar
document.getElementById('bulkAssignBtn').addEventListener('click', function() {
    if (currentUserType && currentUserId) {
        document.getElementById('permisosModal').classList.remove('hidden');
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Sin cambios',
            text: 'No hay cambios para guardar',
            confirmButtonColor: '#6366F1'
        });
    }
});

// Cerrar modal
document.querySelectorAll('[data-dismiss="modal"], .bg-gray-500, .cancel-btn').forEach(el => {
    el.addEventListener('click', function() {
        document.getElementById('permisosModal').classList.add('hidden');
    });
});

// Confirmar guardado
document.getElementById('confirmSave').addEventListener('click', function() {
    let button = this;
    let originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';

    fetch('{{ route("admin.permisos.save") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_type: currentUserType,
            user_id: parseInt(currentUserId),
            permisos: permisosSeleccionados
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('permisosModal').classList.add('hidden');

            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });

            document.getElementById('bulkAssignBtn').classList.add('hidden');
            currentUserType = null;
            currentUserId = null;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Error al guardar los permisos',
                confirmButtonColor: '#6366F1'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al guardar los permisos',
            confirmButtonColor: '#6366F1'
        });
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalHtml;
    });
});

// Cerrar modal haciendo clic fuera
document.querySelector('.bg-gray-500').addEventListener('click', function() {
    document.getElementById('permisosModal').classList.add('hidden');
});
</script>

<style>
.sticky {
    position: sticky;
    left: 0;
    z-index: 10;
}
.permission-checkbox {
    width: 1.2rem;
    height: 1.2rem;
    cursor: pointer;
}
.permission-checkbox:checked {
    background-color: #6366F1;
    border-color: #6366F1;
}
.tab-button.active {
    border-bottom-width: 2px;
}
</style>
@endsection
