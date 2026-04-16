@extends('layouts.cliente.app')

@section('title', 'Perfil - Mi Empresa')
@section('page-title', 'Mi Perfil')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Mensajes de sesión -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($user) && isset($client))
                <!-- Información del perfil -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>
                            Información Personal
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('client.profile.update') }}" method="POST" id="profileForm">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Nombres -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombres" class="form-label">
                                        Nombres <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('nombres') is-invalid @enderror"
                                           id="nombres"
                                           name="nombres"
                                           value="{{ old('nombres', $user->nombres) }}"
                                           required>
                                    @error('nombres')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Apellido Paterno -->
                                <div class="col-md-6 mb-3">
                                    <label for="apellido_paterno" class="form-label">
                                        Apellido Paterno <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('apellido_paterno') is-invalid @enderror"
                                           id="apellido_paterno"
                                           name="apellido_paterno"
                                           value="{{ old('apellido_paterno', $user->apellido_paterno) }}"
                                           required>
                                    @error('apellido_paterno')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Apellido Materno -->
                                <div class="col-md-6 mb-3">
                                    <label for="apellido_materno" class="form-label">
                                        Apellido Materno <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('apellido_materno') is-invalid @enderror"
                                           id="apellido_materno"
                                           name="apellido_materno"
                                           value="{{ old('apellido_materno', $user->apellido_materno) }}"
                                           required>
                                    @error('apellido_materno')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- País -->
                                <div class="col-md-6 mb-3">
                                    <label for="pais" class="form-label">
                                        País <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('pais') is-invalid @enderror"
                                            id="pais"
                                            name="pais"
                                            required>
                                        <option value="">Seleccionar país</option>
                                        @foreach($countries as $code => $name)
                                            <option value="{{ $code }}"
                                                {{ old('pais', $user->pais) == $code ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pais')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tipo de Documento -->
                                <div class="col-md-6 mb-3">
                                    <label for="tipo_documento" class="form-label">
                                        Tipo de Documento <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('tipo_documento') is-invalid @enderror"
                                            id="tipo_documento"
                                            name="tipo_documento"
                                            required>
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($documentTypes as $key => $name)
                                            <option value="{{ $key }}"
                                                {{ old('tipo_documento', $user->tipo_documento) == $key ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tipo_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Número de Documento -->
                                <div class="col-md-6 mb-3">
                                    <label for="nro_documento" class="form-label">
                                        Número de Documento <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('nro_documento') is-invalid @enderror"
                                           id="nro_documento"
                                           name="nro_documento"
                                           value="{{ old('nro_documento', $user->nro_documento) }}"
                                           required>
                                    <small class="text-muted" id="documentFormatHint"></small>
                                    @error('nro_documento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">
                                        Teléfono
                                    </label>
                                    <input type="tel"
                                           class="form-control @error('telefono') is-invalid @enderror"
                                           id="telefono"
                                           name="telefono"
                                           value="{{ old('telefono', $user->telefono) }}">
                                    <small class="text-muted">Ejemplo: +51987654321</small>
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>
                                    Actualizar Perfil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lock me-2"></i>
                            Cambiar Contraseña
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('client.profile.update-password') }}" method="POST" id="passwordForm">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="current_password" class="form-label">
                                        Contraseña Actual <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           class="form-control @error('current_password') is-invalid @enderror"
                                           id="current_password"
                                           name="current_password"
                                           required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Nueva Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password"
                                           required>
                                    <small class="text-muted">Mínimo 6 caracteres</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        Confirmar Nueva Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation"
                                           required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="submit" class="btn btn-outline-primary" id="passwordBtn">
                                    <i class="fas fa-key me-2"></i>
                                    Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <h5>Error al cargar el perfil</h5>
                        <p class="text-muted">No se pudo cargar la información de tu perfil.</p>
                        <a href="{{ route('client.logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="btn btn-primary">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Cerrar Sesión
                        </a>
                        <form id="logout-form" action="{{ route('client.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

/* Estilo para el hint del formato del documento */
#documentFormatHint {
    font-size: 0.75rem;
    display: block;
    margin-top: 0.25rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const paisSelect = document.getElementById('pais');
    const tipoDocumentoSelect = document.getElementById('tipo_documento');
    const documentFormatHint = document.getElementById('documentFormatHint');

    // Diccionario de formatos y ejemplos por tipo de documento
    const documentFormats = {
        'dni': { format: '8 dígitos numéricos', example: '12345678' },
        'cedula': { format: '7 a 12 dígitos numéricos', example: '123456789' },
        'ine': { format: '18 caracteres (letras mayúsculas y números)', example: 'ABCD12345678901234' },
        'pasaporte': { format: '6 a 12 caracteres (letras mayúsculas y números)', example: 'AB123456' },
        'visa': { format: '8 o 9 caracteres (letras mayúsculas y números)', example: 'AB123456' },
        'passport': { format: '6 a 12 caracteres (letras mayúsculas y números)', example: 'AB123456' }
    };

    // Función para actualizar el hint del formato del documento
    function updateDocumentFormatHint() {
        const selectedType = tipoDocumentoSelect.value;
        if (selectedType && documentFormats[selectedType]) {
            const format = documentFormats[selectedType];
            documentFormatHint.innerHTML = `<i class="fas fa-info-circle me-1"></i>Formato: ${format.format}. Ejemplo: ${format.example}`;
            documentFormatHint.style.color = '#6c757d';
        } else {
            documentFormatHint.innerHTML = '';
        }
    }

    // Cambio dinámico de tipos de documento según el país
    if (paisSelect && tipoDocumentoSelect) {
        // Función para cargar tipos de documento
        function loadDocumentTypes(countryCode) {
            if (!countryCode) return;

            // Guardar el valor seleccionado actualmente
            const currentValue = tipoDocumentoSelect.value;

            // Mostrar loading
            tipoDocumentoSelect.disabled = true;
            tipoDocumentoSelect.innerHTML = '<option value="">Cargando...</option>';

            // Obtener tipos de documento via AJAX
            fetch(`/get-document-types/${countryCode}`)
                .then(response => response.json())
                .then(data => {
                    tipoDocumentoSelect.innerHTML = '<option value="">Seleccionar tipo</option>';
                    for (const [key, value] of Object.entries(data)) {
                        const option = document.createElement('option');
                        option.value = key;
                        option.textContent = value;
                        // Mantener el valor seleccionado si aún es válido
                        if (currentValue === key) {
                            option.selected = true;
                        }
                        tipoDocumentoSelect.appendChild(option);
                    }
                    tipoDocumentoSelect.disabled = false;

                    // Actualizar el hint después de cargar
                    updateDocumentFormatHint();
                })
                .catch(error => {
                    console.error('Error:', error);
                    tipoDocumentoSelect.innerHTML = '<option value="">Error al cargar</option>';
                    tipoDocumentoSelect.disabled = false;
                });
        }

        // Escuchar cambios en el país
        paisSelect.addEventListener('change', function() {
            const countryCode = this.value;
            if (countryCode) {
                loadDocumentTypes(countryCode);
            }
        });

        // Escuchar cambios en el tipo de documento para actualizar el hint
        tipoDocumentoSelect.addEventListener('change', updateDocumentFormatHint);

        // Cargar tipos de documento iniciales si hay un país seleccionado
        if (paisSelect.value) {
            loadDocumentTypes(paisSelect.value);
        } else {
            // Actualizar hint inicial
            updateDocumentFormatHint();
        }
    }

    // Confirmación antes de enviar el formulario de perfil
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        });
    }

    // Confirmación antes de enviar el formulario de contraseña
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;

            if (password !== passwordConfirmation) {
                e.preventDefault();
                mostrarNotificacion('Las contraseñas no coinciden', 'danger');
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                mostrarNotificacion('La contraseña debe tener al menos 6 caracteres', 'danger');
                return;
            }

            const submitBtn = document.getElementById('passwordBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cambiando...';
        });
    }
});

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo) {
    const existingNotification = document.querySelector('.custom-notification');
    if (existingNotification) existingNotification.remove();

    const notification = document.createElement('div');
    notification.className = `custom-notification alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '500px';
    notification.style.backgroundColor = tipo === 'danger' ? '#f8d7da' : '#d1e7dd';
    notification.style.borderLeft = `4px solid ${tipo === 'danger' ? '#dc3545' : '#0d6efd'}`;
    notification.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';

    const icon = tipo === 'danger' ? 'times-circle' : 'check-circle';

    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${icon} me-2 ${tipo === 'danger' ? 'text-danger' : 'text-primary'}"></i>
            <div class="flex-grow-1">
                <strong>${tipo === 'danger' ? 'Error' : 'Éxito'}</strong>
                <div class="small">${mensaje}</div>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="this.closest('.custom-notification').remove()"></button>
        </div>
    `;

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>
@endsection
