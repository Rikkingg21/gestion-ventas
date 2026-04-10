<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.2s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5a67d8 0%, #6b46a0 100%);
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body class="gradient-bg min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Cabecera -->
                <div class="text-center mb-4">
                    <h1 class="text-white fw-bold mb-2">Crear Cuenta de Cliente</h1>
                    <p class="text-white-50">Regístrate para acceder a todos nuestros servicios</p>
                </div>

                <!-- Mensajes -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Formulario -->
                <div class="card card-shadow border-0 rounded-4">
                    <div class="card-body p-5">
                        @if($errors->any() && !session('error'))
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Por favor corrige los siguientes errores:
                                </div>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('client.register') }}" id="registerForm">
                            @csrf

                            <div class="row g-4">
                                <!-- Nombres -->
                                <div class="col-12">
                                    <label for="nombres" class="form-label fw-semibold">
                                        Nombres Completos <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user text-secondary"></i>
                                        </span>
                                        <input type="text"
                                               id="nombres"
                                               name="nombres"
                                               value="{{ old('nombres') }}"
                                               class="form-control @error('nombres') is-invalid @enderror"
                                               placeholder="Ej: Juan Carlos"
                                               required>
                                    </div>
                                    @error('nombres')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Apellidos -->
                                <div class="col-md-6">
                                    <label for="apellido_paterno" class="form-label fw-semibold">
                                        Apellido Paterno <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user-tag text-secondary"></i>
                                        </span>
                                        <input type="text"
                                               id="apellido_paterno"
                                               name="apellido_paterno"
                                               value="{{ old('apellido_paterno') }}"
                                               class="form-control @error('apellido_paterno') is-invalid @enderror"
                                               placeholder="Ej: Pérez"
                                               required>
                                    </div>
                                    @error('apellido_paterno')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="apellido_materno" class="form-label fw-semibold">
                                        Apellido Materno <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-user-friends text-secondary"></i>
                                        </span>
                                        <input type="text"
                                               id="apellido_materno"
                                               name="apellido_materno"
                                               value="{{ old('apellido_materno') }}"
                                               class="form-control @error('apellido_materno') is-invalid @enderror"
                                               placeholder="Ej: García"
                                               required>
                                    </div>
                                    @error('apellido_materno')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-12">
                                    <label for="email" class="form-label fw-semibold">
                                        Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-envelope text-secondary"></i>
                                        </span>
                                        <input type="email"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               class="form-control @error('email') is-invalid @enderror"
                                               placeholder="Ej: juan.perez@email.com"
                                               required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- País -->
                                <div class="col-md-6">
                                    <label for="pais" class="form-label fw-semibold">
                                        País <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-globe-americas text-secondary"></i>
                                        </span>
                                        <select id="pais"
                                                name="pais"
                                                class="form-select @error('pais') is-invalid @enderror"
                                                required>
                                            <option value="">Seleccionar País</option>
                                            @foreach($countries as $code => $name)
                                                <option value="{{ $code }}" {{ old('pais') == $code ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('pais')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tipo y Número de Documento -->
                                <div class="col-md-6">
                                    <label for="tipo_documento" class="form-label fw-semibold">
                                        Tipo de Documento <span class="text-danger">*</span>
                                    </label>
                                    <select id="tipo_documento"
                                            name="tipo_documento"
                                            class="form-select @error('tipo_documento') is-invalid @enderror"
                                            required>
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($documentTypes as $key => $name)
                                            <option value="{{ $key }}" {{ old('tipo_documento') == $key ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tipo_documento')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="nro_documento" class="form-label fw-semibold">
                                        Número de Documento <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           id="nro_documento"
                                           name="nro_documento"
                                           value="{{ old('nro_documento') }}"
                                           class="form-control @error('nro_documento') is-invalid @enderror"
                                           placeholder="Ingresa tu número de documento"
                                           required>
                                    @error('nro_documento')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label fw-semibold">
                                        Teléfono <span class="text-muted small">(opcional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-phone text-secondary"></i>
                                        </span>
                                        <input type="tel"
                                               id="telefono"
                                               name="telefono"
                                               value="{{ old('telefono') }}"
                                               class="form-control @error('telefono') is-invalid @enderror"
                                               placeholder="Ej: 999888777">
                                    </div>
                                    @error('telefono')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Contraseña -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">
                                        Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock text-secondary"></i>
                                        </span>
                                        <input type="password"
                                               id="password"
                                               name="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               placeholder="Mínimo 6 caracteres"
                                               required>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-semibold">
                                        Confirmar Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-check-circle text-secondary"></i>
                                        </span>
                                        <input type="password"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               class="form-control"
                                               placeholder="Repite tu contraseña"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <!-- Términos -->
                            <div class="mt-4">
                                <div class="form-check">
                                    <input type="checkbox"
                                           id="terminos"
                                           name="terminos"
                                           value="1"
                                           {{ old('terminos') ? 'checked' : '' }}
                                           class="form-check-input @error('terminos') is-invalid @enderror"
                                           required>
                                    <label class="form-check-label" for="terminos">
                                        Acepto los <a href="#" class="text-decoration-none">Términos y Condiciones</a> y la
                                        <a href="#" class="text-decoration-none">Política de Privacidad</a>
                                        <span class="text-danger">*</span>
                                    </label>
                                    @error('terminos')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Botón -->
                            <div class="mt-4">
                                <button type="submit"
                                        class="btn btn-primary-custom btn-lg w-100 text-white py-2 fw-semibold"
                                        id="btnRegister">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Registrarme como Cliente
                                </button>
                            </div>
                        </form>

                        <!-- Enlace a login -->
                        <div class="mt-4 text-center pt-3 border-top">
                            <p class="text-muted mb-0">
                                ¿Ya tienes una cuenta?
                                <a href="{{ route('client.login') }}" class="text-decoration-none fw-semibold">
                                    Inicia sesión aquí
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Placeholders por tipo de documento
        const placeholders = {
            'dni': 'Ej: 12345678',
            'cedula': 'Ej: 12345678',
            'ine': 'Ej: ABC123456789012345',
            'pasaporte': 'Ej: ABC123456',
            'visa': 'Ej: ABC123456',
            'passport': 'Ej: ABC123456'
        };

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const btnRegister = document.getElementById('btnRegister');
            const paisSelect = document.getElementById('pais');
            const tipoDocumentoSelect = document.getElementById('tipo_documento');
            const nroDocumento = document.getElementById('nro_documento');

            // Prevenir doble envío
            form.addEventListener('submit', function(e) {
                if (btnRegister.disabled) {
                    e.preventDefault();
                    return;
                }
                btnRegister.disabled = true;
                btnRegister.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';

                setTimeout(() => {
                    btnRegister.disabled = false;
                    btnRegister.innerHTML = '<i class="fas fa-user-plus me-2"></i>Registrarme como Cliente';
                }, 5000);
            });

            // Cargar tipos de documento al cambiar país
            paisSelect.addEventListener('change', function() {
                const countryCode = this.value;

                if (countryCode) {
                    fetch(`/get-document-types/${countryCode}`)
                        .then(response => response.json())
                        .then(data => {
                            tipoDocumentoSelect.innerHTML = '<option value="">Seleccionar tipo de documento</option>';

                            Object.entries(data).forEach(([key, value]) => {
                                const option = document.createElement('option');
                                option.value = key;
                                option.textContent = value;
                                tipoDocumentoSelect.appendChild(option);
                            });

                            nroDocumento.value = '';
                            nroDocumento.placeholder = 'Ingresa tu número de documento';
                        })
                        .catch(error => console.error('Error:', error));
                }
            });

            // Actualizar placeholder al cambiar tipo de documento
            tipoDocumentoSelect.addEventListener('change', function() {
                const type = this.value;
                if (placeholders[type]) {
                    nroDocumento.placeholder = placeholders[type];
                } else {
                    nroDocumento.placeholder = 'Ingresa tu número de documento';
                }
            });

            // Trigger inicial
            if (paisSelect.value) {
                paisSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
