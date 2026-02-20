@extends('layouts.admin.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario: ' . $user->nombre_completo)

@section('content')
@php
    $adminUser = Auth::guard('admin')->user();
    $currentUser = $adminUser->user;
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Editar Usuario</h2>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" id="userForm">
                    @csrf
                    @method('PUT')

                    <!-- Tipo de Usuario (solo lectura para edición) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Roles del Usuario</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="relative">
                                <input type="checkbox" name="user_types[]" value="admin" class="sr-only peer"
                                    {{ in_array('admin', $userTypes) ? 'checked' : '' }} disabled>
                                <div class="p-4 border rounded-lg {{ in_array('admin', $userTypes) ? 'border-indigo-500 ring-2 ring-indigo-500 bg-indigo-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2">
                                            <i class="fas fa-user-tie text-indigo-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Administrador</p>
                                            @if(in_array('admin', $userTypes))
                                                <p class="text-xs text-indigo-600">
                                                    <i class="fas fa-check-circle mr-1"></i> Rol activo
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative">
                                <input type="checkbox" name="user_types[]" value="staff" class="sr-only peer"
                                    {{ in_array('staff', $userTypes) ? 'checked' : '' }} disabled>
                                <div class="p-4 border rounded-lg {{ in_array('staff', $userTypes) ? 'border-emerald-500 ring-2 ring-emerald-500 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-emerald-100 rounded-lg p-2">
                                            <i class="fas fa-users text-emerald-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Staff</p>
                                            @if(in_array('staff', $userTypes))
                                                <p class="text-xs text-emerald-600">
                                                    <i class="fas fa-check-circle mr-1"></i> Rol activo
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative">
                                <input type="checkbox" name="user_types[]" value="client" class="sr-only peer"
                                    {{ in_array('client', $userTypes) ? 'checked' : '' }} disabled>
                                <div class="p-4 border rounded-lg {{ in_array('client', $userTypes) ? 'border-amber-500 ring-2 ring-amber-500 bg-amber-50' : 'border-gray-200 bg-gray-50' }}">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-amber-100 rounded-lg p-2">
                                            <i class="fas fa-user text-amber-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Cliente</p>
                                            @if(in_array('client', $userTypes))
                                                <p class="text-xs text-amber-600">
                                                    <i class="fas fa-check-circle mr-1"></i> Rol activo
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inputs ocultos para mantener los valores actuales -->
                        @foreach($userTypes as $type)
                            <input type="hidden" name="existing_user_types[]" value="{{ $type }}">
                        @endforeach

                        <p class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los roles del usuario se muestran en modo consulta. Para modificar roles, contacta al administrador.
                        </p>
                    </div>

                    <!-- Datos Básicos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario <span class="text-red-500">*</span></label>
                            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $user->nombres) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('nombres')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno <span class="text-red-500">*</span></label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('apellido_paterno')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $user->apellido_materno) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('apellido_materno')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('telefono')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Documento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento <span class="text-red-500">*</span></label>
                            <select name="tipo_documento" id="tipo_documento"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required>
                                <option value="">Seleccionar</option>
                                @foreach($tiposDocumento as $value => $label)
                                    <option value="{{ $value }}" {{ old('tipo_documento', $user->tipo_documento) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('tipo_documento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nro_documento" class="block text-sm font-medium text-gray-700 mb-1">Número de Documento <span class="text-red-500">*</span></label>
                            <input type="text" name="nro_documento" id="nro_documento" value="{{ old('nro_documento', $user->nro_documento) }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('nro_documento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Contraseña (opcional en edición) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                            <input type="password" name="password" id="password"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Dejar en blanco para mantener la contraseña actual. Mínimo 8 caracteres si se cambia.</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Panel Admin - Se muestra si el usuario tiene rol admin -->
                    @if(in_array('admin', $userTypes))
                    <div id="admin-panel" class="panel-type bg-indigo-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Administrador</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nivel_admin" class="block text-sm font-medium text-gray-700 mb-1">Nivel <span class="text-red-500">*</span></label>
                                <select name="nivel_admin" id="nivel_admin" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Seleccionar</option>
                                    @foreach($nivelesAdmin as $value => $label)
                                        <option value="{{ $value }}" {{ old('nivel_admin', $user->admin->nivel ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="is_active_admin" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active_admin" id="is_active_admin" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="1" {{ old('is_active_admin', $user->admin->is_active ?? 1) == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active_admin', $user->admin->is_active ?? 1) == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Panel Staff -->
                    @if(in_array('staff', $userTypes))
                    <div id="staff-panel" class="panel-type bg-emerald-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Staff</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="cargo_staff" class="block text-sm font-medium text-gray-700 mb-1">Cargo <span class="text-red-500">*</span></label>
                                <select name="cargo_staff" id="cargo_staff" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="">Seleccionar</option>
                                    @foreach($cargosStaff as $value => $label)
                                        <option value="{{ $value }}" {{ old('cargo_staff', $user->staff->cargo ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="area_staff" class="block text-sm font-medium text-gray-700 mb-1">Área <span class="text-red-500">*</span></label>
                                <select name="area_staff" id="area_staff" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="">Seleccionar</option>
                                    @foreach($areasStaff as $value => $label)
                                        <option value="{{ $value }}" {{ old('area_staff', $user->staff->area ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="fecha_contratacion" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Contratación <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_contratacion" id="fecha_contratacion"
                                    value="{{ old('fecha_contratacion', $user->staff && $user->staff->fecha_contratacion ? $user->staff->fecha_contratacion->format('Y-m-d') : '') }}"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="salario_staff" class="block text-sm font-medium text-gray-700 mb-1">Salario</label>
                                <input type="number" step="0.01" name="salario_staff" id="salario_staff"
                                    value="{{ old('salario_staff', $user->staff->salario ?? '') }}"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label for="is_active_staff" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active_staff" id="is_active_staff" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="1" {{ old('is_active_staff', $user->staff->is_active ?? 1) == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active_staff', $user->staff->is_active ?? 1) == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Panel Cliente - Se muestra si el usuario tiene rol client -->
                    @if(in_array('client', $userTypes))
                    <div id="client-panel" class="panel-type bg-amber-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Cliente</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="is_active_client" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active_client" id="is_active_client" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                    <option value="1" {{ old('is_active_client', $user->client->is_active ?? 1) == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active_client', $user->client->is_active ?? 1) == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // No hay necesidad de cambiar paneles porque el tipo es fijo
    // Solo aseguramos que los campos correctos tengan required
    const userTypes = @json($userTypes);

    if (userTypes.includes('admin')) {
        document.getElementById('nivel_admin').required = true;
    }

    if (userTypes.includes('staff')) {
        document.getElementById('cargo_staff').required = true;
        document.getElementById('area_staff').required = true;
        document.getElementById('fecha_contratacion').required = true;
    }
});
</script>

<style>
.peer:checked + div {
    border-width: 2px;
}
</style>
@endsection
