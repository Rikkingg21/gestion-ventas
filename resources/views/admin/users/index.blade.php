@extends('layouts.admin.app')

@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')

@section('content')
@php
    $currentUser = Auth::guard('admin')->user();
@endphp
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header con botón de agregar -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestión de Usuarios</h1>
                    <p class="mt-1 text-sm text-gray-600">Administra los usuarios del sistema</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-users mr-2"></i>
                        Total: {{ $admins->count() + $staffs->count() }}
                    </span>
                    @if($currentUser->canCreate('users'))
                    <a href="{{ route('admin.users.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Usuario
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cards de resumen -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px" aria-label="Tabs">
                    <button onclick="switchTab('admin')"
                            class="tab-button active inline-flex items-center px-6 py-4 border-b-2 border-indigo-500 text-sm font-medium text-indigo-600 bg-indigo-50"
                            id="admin-tab">
                        <i class="fas fa-user-tie mr-2"></i>
                        Administradores
                        <span class="ml-3 bg-indigo-100 text-indigo-600 py-0.5 px-2 rounded-full text-xs">
                            {{ $admins->count() }}
                        </span>
                    </button>
                    <button onclick="switchTab('staff')"
                            class="tab-button inline-flex items-center px-6 py-4 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            id="staff-tab">
                        <i class="fas fa-users mr-2"></i>
                        Staff
                        <span class="ml-3 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                            {{ $staffs->count() }}
                        </span>
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Panel de Administradores -->
                <div id="admin-panel" class="tab-panel">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($admins as $admin)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($admin->user->nombres, 0, 1)) }}{{ strtoupper(substr($admin->user->apellido_paterno, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $admin->user->username }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $admin->user->nombre_completo }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $admin->user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $admin->user->tipo_documento }}: {{ $admin->user->nro_documento }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $admin->user->telefono ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($admin->isSuperAdmin())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                <i class="fas fa-crown mr-1"></i> Super Admin
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <i class="fas fa-user-tie mr-1"></i> Admin
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($admin->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($currentUser->canUpdate('users'))
                                            <a href="{{ route('admin.users.edit', $admin->user_id) }}"
                                               class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-full transition-colors duration-200"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif

                                            @if($currentUser->canDelete('users') && $adminUser->user_id != $admin->user_id)
                                                @if(!$admin->isSuperAdmin() || $currentUser->isSuperAdmin())
                                                <button type="button"
                                                        onclick="confirmDelete({{ $admin->user_id }}, '{{ $admin->user->nombre_completo }}')"
                                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-full transition-colors duration-200"
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gray-100 rounded-full p-3 mb-3">
                                                <i class="fas fa-user-tie text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500 font-medium">No hay administradores registrados</p>
                                            @if($currentUser->canCreate('users'))
                                            <a href="{{ route('admin.users.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                <i class="fas fa-plus mr-2"></i>
                                                Crear Administrador
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Panel de Staff -->
                <div id="staff-panel" class="tab-panel hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($staffs as $staff)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($staff->user->nombres, 0, 1)) }}{{ strtoupper(substr($staff->user->apellido_paterno, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $staff->user->username }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $staff->user->nombre_completo }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $staff->user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $staff->user->tipo_documento }}: {{ $staff->user->nro_documento }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $staff->user->telefono ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $staff->cargo }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $staff->area }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($staff->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if($currentUser->canUpdate('users'))
                                            <a href="{{ route('admin.users.edit', $staff->user_id) }}"
                                               class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-full transition-colors duration-200"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif

                                            @if($currentUser->canDelete('users') && $adminUser->user_id != $staff->user_id)
                                            <button type="button"
                                                    onclick="confirmDelete({{ $staff->user_id }}, '{{ $staff->user->nombre_completo }}')"
                                                    class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-full transition-colors duration-200"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gray-100 rounded-full p-3 mb-3">
                                                <i class="fas fa-users text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="text-gray-500 font-medium">No hay staff registrado</p>
                                            @if($currentUser->canCreate('users'))
                                            <a href="{{ route('admin.users.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                <i class="fas fa-plus mr-2"></i>
                                                Crear Staff
                                            </a>
                                            @endif
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

<!-- Modal de confirmación de eliminación -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirmar Eliminación
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                ¿Estás seguro de eliminar el usuario <span id="userName" class="font-semibold"></span>?
                            </p>
                            <p class="text-sm text-red-600 mt-2">
                                <strong>Advertencia:</strong> Esta acción no se puede deshacer.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-trash mr-2"></i>
                        Eliminar
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
}

function confirmDelete(userId, userName) {
    document.getElementById('userName').textContent = userName;
    // Usando route() con parámetro
    let url = '{{ route("admin.users.destroy", ":id") }}';
    url = url.replace(':id', userId);
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Cerrar modal haciendo clic fuera
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('bg-gray-500')) {
        closeDeleteModal();
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<style>
.tab-button.active {
    border-bottom-width: 2px;
}
</style>
@endsection
