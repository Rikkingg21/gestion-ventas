@extends('layouts.admin.app')

@section('title', 'Crear Usuario')
@section('page-title', 'Crear Nuevo Usuario')

@section('content')
@php
    $adminUser = Auth::guard('admin')->user();
    $currentUser = $adminUser->user;
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Información del Usuario</h2>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.users.store') }}" id="userForm">
                    @csrf

                    <!-- Tipo de Usuario -->
                    <div class="mb-6">
                        <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Buscar Usuario Existente</h3>
                            <p class="text-sm text-gray-600 mb-4">Si el usuario ya tiene un documento registrado, puedes buscarlo para auto-llenar los datos.</p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label for="search_tipo_documento" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento</label>
                                    <select id="search_tipo_documento" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach($tiposDocumento as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="search_nro_documento" class="block text-sm font-medium text-gray-700 mb-1">Número de Documento</label>
                                    <input type="text" id="search_nro_documento"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Ingrese número">
                                </div>

                                <div>
                                    <button type="button" id="searchUserBtn"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                                        <i class="fas fa-search mr-2"></i>
                                        Buscar Usuario
                                    </button>
                                </div>
                            </div>

                            <!-- Mensaje de resultado -->
                            <div id="searchMessage" class="mt-3 hidden"></div>

                            <!-- Botón para limpiar búsqueda -->
                            <div id="clearSearchContainer" class="mt-3 hidden">
                                <button type="button" id="clearSearchBtn" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-undo-alt mr-1"></i>
                                    Limpiar búsqueda y reiniciar formulario
                                </button>
                            </div>
                        </div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Usuario</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="relative block cursor-pointer">
                                <input type="radio" name="user_type" value="admin" class="sr-only peer" checked>
                                <div class="p-4 border rounded-lg peer-checked:border-indigo-500 peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:bg-indigo-50">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2">
                                            <i class="fas fa-user-tie text-indigo-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Administrador</p>
                                            <p class="text-xs text-gray-500">Acceso completo al panel</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative block cursor-pointer">
                                <input type="radio" name="user_type" value="staff" class="sr-only peer">
                                <div class="p-4 border rounded-lg peer-checked:border-emerald-500 peer-checked:ring-2 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-emerald-100 rounded-lg p-2">
                                            <i class="fas fa-users text-emerald-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">Staff</p>
                                            <p class="text-xs text-gray-500">Personal de la empresa</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('user_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Datos Básicos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario <span class="text-red-500">*</span></label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                            <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('nombres')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno <span class="text-red-500">*</span></label>
                            <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('apellido_paterno')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('apellido_materno')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
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
                                    <option value="{{ $value }}" {{ old('tipo_documento') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('tipo_documento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nro_documento" class="block text-sm font-medium text-gray-700 mb-1">Número de Documento <span class="text-red-500">*</span></label>
                            <input type="text" name="nro_documento" id="nro_documento" value="{{ old('nro_documento') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            @error('nro_documento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="password"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   required>
                        </div>
                    </div>

                    <!-- Panel Admin -->
                    <div id="admin-panel" class="panel-type bg-indigo-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Administrador</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nivel_admin" class="block text-sm font-medium text-gray-700 mb-1">Nivel <span class="text-red-500">*</span></label>
                                <select name="nivel_admin" id="nivel_admin" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Seleccionar</option>
                                    @foreach($nivelesAdmin as $value => $label)
                                        <option value="{{ $value }}" {{ old('nivel_admin') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="is_active_admin" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active_admin" id="is_active_admin" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="1" {{ old('is_active_admin', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active_admin') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Staff -->
                    <div id="staff-panel" class="panel-type bg-emerald-50 p-4 rounded-lg mb-6 hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Staff</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="cargo_staff" class="block text-sm font-medium text-gray-700 mb-1">Cargo <span class="text-red-500">*</span></label>
                                <select name="cargo_staff" id="cargo_staff" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="">Seleccionar</option>
                                    @foreach($cargosStaff as $value => $label)
                                        <option value="{{ $value }}" {{ old('cargo_staff') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="area_staff" class="block text-sm font-medium text-gray-700 mb-1">Área <span class="text-red-500">*</span></label>
                                <select name="area_staff" id="area_staff" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="">Seleccionar</option>
                                    @foreach($areasStaff as $value => $label)
                                        <option value="{{ $value }}" {{ old('area_staff') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="fecha_contratacion" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Contratación <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_contratacion" id="fecha_contratacion" value="{{ old('fecha_contratacion') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="salario_staff" class="block text-sm font-medium text-gray-700 mb-1">Salario</label>
                                <input type="number" step="0.01" name="salario_staff" id="salario_staff" value="{{ old('salario_staff') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                                       placeholder="0.00">
                            </div>
                            <div>
                                <label for="is_active_staff" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active_staff" id="is_active_staff" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                                    <option value="1" {{ old('is_active_staff', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active_staff') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Cliente -->
                    <div id="client-panel" class="panel-type bg-amber-50 p-4 rounded-lg mb-6 hidden">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración de Cliente</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="is_active_client" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="is_active_client" id="is_active_client" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                                    <option value="1" {{ old('is_active_client', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active_client') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
    const adminPanel = document.getElementById('admin-panel');
    const staffPanel = document.getElementById('staff-panel');
    const clientPanel = document.getElementById('client-panel');

    // Elementos requeridos según el tipo
    const adminFields = ['nivel_admin'];
    const staffFields = ['cargo_staff', 'area_staff', 'fecha_contratacion'];

    function togglePanels() {
        const selectedType = document.querySelector('input[name="user_type"]:checked')?.value;

        // Ocultar todos los paneles
        adminPanel.classList.add('hidden');
        staffPanel.classList.add('hidden');
        clientPanel.classList.add('hidden');

        // Remover required de todos los campos específicos
        adminFields.forEach(field => {
            const input = document.getElementById(field);
            if (input) input.required = false;
        });

        staffFields.forEach(field => {
            const input = document.getElementById(field);
            if (input) input.required = false;
        });

        // Mostrar panel correspondiente y agregar required
        if (selectedType === 'admin') {
            adminPanel.classList.remove('hidden');
            adminFields.forEach(field => {
                const input = document.getElementById(field);
                if (input) input.required = true;
            });
        } else if (selectedType === 'staff') {
            staffPanel.classList.remove('hidden');
            staffFields.forEach(field => {
                const input = document.getElementById(field);
                if (input) input.required = true;
            });
        } else if (selectedType === 'client') {
            clientPanel.classList.remove('hidden');
        }
    }

    userTypeRadios.forEach(radio => {
        radio.addEventListener('change', togglePanels);
    });

    // Ejecutar al inicio para mostrar el panel correcto
    togglePanels();

    // ===== FUNCIONALIDAD DEL BUSCADOR =====
    const searchBtn = document.getElementById('searchUserBtn');
    const searchTipoDocumento = document.getElementById('search_tipo_documento');
    const searchNroDocumento = document.getElementById('search_nro_documento');
    const searchMessage = document.getElementById('searchMessage');
    const clearSearchContainer = document.getElementById('clearSearchContainer');

    // Guardar valores originales para poder limpiar
    let originalFormData = null;

    function fillFormWithUserData(userData) {
        document.getElementById('username').value = userData.username || '';
        document.getElementById('nombres').value = userData.nombres || '';
        document.getElementById('apellido_paterno').value = userData.apellido_paterno || '';
        document.getElementById('apellido_materno').value = userData.apellido_materno || '';
        document.getElementById('email').value = userData.email || '';
        document.getElementById('telefono').value = userData.telefono || '';

        // Mostrar mensaje de éxito
        searchMessage.className = 'mt-3 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-600';
        searchMessage.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Usuario encontrado. Los datos han sido auto-llenados.';
        searchMessage.classList.remove('hidden');

        // Mostrar botón para limpiar
        clearSearchContainer.classList.remove('hidden');
    }

    function clearForm() {
        document.getElementById('username').value = '';
        document.getElementById('nombres').value = '';
        document.getElementById('apellido_paterno').value = '';
        document.getElementById('apellido_materno').value = '';
        document.getElementById('email').value = '';
        document.getElementById('telefono').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password_confirmation').value = '';

        // Limpiar selects
        document.getElementById('tipo_documento').value = '';
        document.getElementById('nro_documento').value = '';
        document.getElementById('search_tipo_documento').value = '';
        document.getElementById('search_nro_documento').value = '';

        // Ocultar mensajes
        searchMessage.classList.add('hidden');
        clearSearchContainer.classList.add('hidden');
    }

    searchBtn.addEventListener('click', function() {
        const tipoDocumento = searchTipoDocumento.value;
        const nroDocumento = searchNroDocumento.value.trim();

        if (!tipoDocumento || !nroDocumento) {
            searchMessage.className = 'mt-3 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-600';
            searchMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Debe seleccionar tipo de documento e ingresar número.';
            searchMessage.classList.remove('hidden');
            return;
        }

        // Deshabilitar botón mientras se busca
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Buscando...';

        // Guardar datos originales si es primera vez
        if (!originalFormData) {
            originalFormData = {
                username: document.getElementById('username').value,
                nombres: document.getElementById('nombres').value,
                apellido_paterno: document.getElementById('apellido_paterno').value,
                apellido_materno: document.getElementById('apellido_materno').value,
                email: document.getElementById('email').value,
                telefono: document.getElementById('telefono').value,
                tipo_documento: document.getElementById('tipo_documento').value,
                nro_documento: document.getElementById('nro_documento').value
            };
        }

        // Hacer la petición AJAX
        fetch('{{ route("admin.users.search-by-document") }}?tipo_documento=' + encodeURIComponent(tipoDocumento) + '&nro_documento=' + encodeURIComponent(nroDocumento))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Auto-llenar el campo de tipo y número de documento en el formulario principal
                    document.getElementById('tipo_documento').value = tipoDocumento;
                    document.getElementById('nro_documento').value = nroDocumento;

                    // Llenar el resto de campos
                    fillFormWithUserData(data.user);
                } else {
                    searchMessage.className = 'mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md text-sm text-yellow-600';
                    searchMessage.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Usuario no encontrado. Puede continuar con el registro.';
                    searchMessage.classList.remove('hidden');
                    clearSearchContainer.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                searchMessage.className = 'mt-3 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-600';
                searchMessage.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Error al buscar el usuario. Intente nuevamente.';
                searchMessage.classList.remove('hidden');
            })
            .finally(() => {
                // Restaurar botón
                searchBtn.disabled = false;
                searchBtn.innerHTML = '<i class="fas fa-search mr-2"></i> Buscar Usuario';
            });
    });

    // Botón para limpiar búsqueda
    document.getElementById('clearSearchBtn').addEventListener('click', function() {
        clearForm();
    });

    // También permitir búsqueda con Enter en el campo de número de documento
    searchNroDocumento.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });
});
</script>

<style>
.peer:checked + div {
    border-width: 2px;
}
</style>
@endsection
