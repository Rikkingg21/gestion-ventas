<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Empresa - Clientes')</title>

    <link rel="stylesheet" href="{{ asset('scss/custom.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Flag Icons CSS (para banderas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">

    <style>
        body {
            background-color: var(--bg-light);
            padding-top: 70px; /* Altura del navbar fijo */
        }

        /* Navbar fijo en la parte superior */
        .navbar.fixed-top {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: bold;
        }

        .nav-link {
            color: #4b5563;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.6rem 1.5rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f0fdf4;
            color: var(--primary-color);
        }

        .dropdown-item i {
            width: 20px;
            color: var(--primary-color);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .footer {
            background-color: white;
            border-top: 1px solid #dee2e6;
            margin-top: auto;
        }

        .main-content {
            min-height: calc(100vh - 140px);
        }

        /* Estilos para dropdowns */
        .navbar .dropdown-menu {
            z-index: 10000 !important;
            position: absolute !important;
            top: 100% !important;
            margin-top: 0.5rem !important;
        }

        /* Dropdown específico para moneda (a la izquierda) */
        #currencyDropdown + .dropdown-menu {
            right: auto !important;
            left: 0 !important;
            min-width: 320px !important;
        }

        /* Dropdown específico para usuario (a la derecha) */
        #userDropdown + .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            min-width: 200px !important;
        }

        /* Para móviles */
        @media (max-width: 991.98px) {
            body {
                padding-top: 56px; /* Altura del navbar en móvil */
            }

            .navbar-nav .dropdown-menu {
                position: static !important;
                float: none !important;
                width: auto !important;
                margin-top: 0 !important;
                background-color: transparent !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            #currencyDropdown + .dropdown-menu,
            #userDropdown + .dropdown-menu {
                right: auto !important;
                left: 0 !important;
                min-width: 100% !important;
            }
        }

        /* Bandera en el selector de moneda */
        .flag-icon {
            border-radius: 3px;
            box-shadow: 0 0 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <header class="navbar navbar-expand-md navbar-dark bd-navbar">
        <!-- Navbar FIJO en la parte superior -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="fas fa-building me-2"></i>
                    Mi Empresa
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <!-- Módulos Públicos -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}">
                                <i class="fas fa-home me-1"></i>Inicio
                            </a>
                        </li>

                        @if(isset($publicModules) && $publicModules->count() > 0)
                            @foreach($publicModules as $module)
                                @if($module->children->count() > 0)
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#"
                                        id="navbarDropdown{{ $module->id }}"
                                        role="button" data-bs-toggle="dropdown">
                                            <i class="fas {{ $module->icon ?: 'fa-cube' }} me-1"></i>
                                            {{ $module->name }}
                                        </a>
                                        <ul class="dropdown-menu">
                                            @foreach($module->children as $child)
                                                <li>
                                                    <a class="dropdown-item" href="{{ $child->route ?: '#' }}">
                                                        <i class="fas {{ $child->icon ?: 'fa-circle' }} fa-xs me-2"></i>
                                                        {{ $child->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ $module->route ?: '#' }}">
                                            <i class="fas {{ $module->icon ?: 'fa-cube' }} me-1"></i>
                                            {{ $module->name }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif

                        <!-- Módulos con permisos -->
                        @if(isset($isAuthenticated) && $isAuthenticated && isset($menuModules) && $menuModules->count() > 0)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenu"
                                role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-cog me-1"></i>Mi Panel
                                </a>
                                <ul class="dropdown-menu">
                                    @foreach($menuModules as $module)
                                        @if($module->children->count() > 0)
                                            <li class="dropdown-submenu">
                                                <a class="dropdown-item dropdown-toggle" href="#">
                                                    <i class="fas {{ $module->icon ?: 'fa-folder' }} me-2"></i>
                                                    {{ $module->name }}
                                                </a>
                                                <ul class="dropdown-menu">
                                                    @foreach($module->children as $child)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ $child->route }}">
                                                                <i class="fas {{ $child->icon ?: 'fa-circle' }} fa-xs me-2"></i>
                                                                {{ $child->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @else
                                            <li>
                                                <a class="dropdown-item" href="{{ $module->route }}">
                                                    <i class="fas {{ $module->icon ?: 'fa-circle' }} me-2"></i>
                                                    {{ $module->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>

                    <!-- Selector de moneda -->
                    <ul class="navbar-nav me-3">
                        @if(isset($userGeoInfo) && isset($userGeoInfo['country']) && isset($userGeoInfo['currency']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                            id="currencyDropdown" role="button" data-bs-toggle="dropdown">
                                <span class="me-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    @if(isset($userGeoInfo['country']['code']) && $userGeoInfo['country']['code'] !== 'INT')
                                        <span class="flag-icon flag-icon-{{ strtolower($userGeoInfo['country']['code']) }} me-1"></span>
                                    @else
                                        <span class="flag-icon flag-icon-un me-1"></span>
                                    @endif
                                </span>
                                <span class="d-none d-lg-inline">
                                    {{ $userGeoInfo['country']['name'] ?? 'Internacional' }} -
                                    ({{ $userGeoInfo['currency']['codigo'] ?? 'USD' }})
                                </span>
                                <span class="d-lg-none">
                                    {{ $userGeoInfo['currency']['simbolo'] ?? '$' }}
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                                <li>
                                    <div class="dropdown-item-text">
                                        <div class="d-flex align-items-center">
                                            @if(isset($userGeoInfo['country']['code']) && $userGeoInfo['country']['code'] !== 'INT')
                                                <span class="flag-icon flag-icon-{{ strtolower($userGeoInfo['country']['code']) }} me-3" style="font-size: 2rem;"></span>
                                            @else
                                                <span class="flag-icon flag-icon-un me-3" style="font-size: 2rem;"></span>
                                            @endif
                                            <div>
                                                <strong>{{ $userGeoInfo['country']['name'] ?? 'Internacional' }}</strong><br>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="dropdown-item-text">
                                        <div class="mb-2"><strong>Moneda actual:</strong></div>
                                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                            <div>
                                                <span class="fw-bold h5 mb-0">{{ $userGeoInfo['currency']['nombre'] ?? 'Dólar Americano' }}</span><br>
                                                <small class="text-muted">{{ $userGeoInfo['currency']['codigo'] ?? 'USD' }}</small>
                                            </div>
                                            <span class="h2 mb-0 text-primary">{{ $userGeoInfo['currency']['simbolo'] ?? '$' }}</span>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="dropdown-item-text">
                                        <div class="mb-2"><strong>Cambiar moneda:</strong></div>
                                        <div class="row g-1">
                                            @forelse($monedasDisponibles as $moneda)
                                                <div class="col-6">
                                                    <button type="button"
                                                            class="btn btn-outline-primary btn-sm w-100 mb-1
                                                                {{ isset($userGeoInfo['currency']['id']) && $userGeoInfo['currency']['id'] == $moneda->id ? 'active' : '' }}"
                                                            onclick="cambiarMoneda('{{ $moneda->codigo_iso }}')"
                                                            {{ isset($userGeoInfo['currency']['id']) && $userGeoInfo['currency']['id'] == $moneda->id ? 'disabled' : '' }}>
                                                        <span class="flag-icon flag-icon-{{ strtolower($moneda->pais_code ?? 'us') }} me-1"></span>
                                                        {{ $moneda->simbolo }} {{ $moneda->codigo_iso }}
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <p class="text-muted text-center mb-0">No hay monedas disponibles</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        @endif
                    </ul>

                    <!-- Menú de usuario -->
                    <ul class="navbar-nav">
                        @if(isset($isAuthenticated) && $isAuthenticated)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->nombres ?? 'Usuario') }}&background=065f46&color=fff"
                                        alt="Avatar"
                                        class="user-avatar me-2">
                                    <span class="d-none d-lg-inline">{{ $currentUser->nombres ?? 'Mi Cuenta' }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow"
                                    aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('client.profile.edit') }}">
                                            <i class="fas fa-user me-2"></i>Mi Perfil
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form method="POST" action="{{ route('client.logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger py-2">
                                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('client.login') }}" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('client.register') }}" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Registrarse
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Modal de confirmación para cambio de moneda -->
        <div class="modal fade" id="confirmarCambioMonedaModal" tabindex="-1" aria-labelledby="confirmarCambioMonedaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="confirmarCambioMonedaModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ¿Cambiar moneda?
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalCuponInfo" class="alert alert-danger">
                            <i class="fas fa-tag me-2"></i>
                            <strong id="cuponCodigo"></strong><br>
                            <span id="cuponDescuento"></span>
                            <p class="mt-2 mb-0 small" id="cuponMensajePerdida"></p>
                        </div>
                        <p class="mb-0">¿Deseas continuar con el cambio de moneda?</p>
                        <p class="text-danger small mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Al cambiar de moneda, perderás el descuento del cupón.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelarCambioBtn">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-warning" id="confirmarCambioBtn">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Continuar con el cambio
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <span class="text-secondary">
                        &copy; {{ date('Y') }} Mi Empresa. Todos los derechos reservados.
                    </span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-secondary me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="text-secondary"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/carrito.js') }}"></script>
    <script>
        // Variable para almacenar la moneda pendiente de cambio
        let pendingCurrencyChange = null;
        let pendingButton = null;
        let pendingButtonOriginalText = '';

        function cambiarMoneda(currencyCode) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;

            // Mostrar estado de carga
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;

            fetch('/cambiar-moneda', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ currency: currencyCode })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cambio exitoso sin necesidad de confirmación
                    mostrarNotificacion(data.message, data.message_type || 'success');
                    setTimeout(() => location.reload(), 1500);
                } else if (data.requires_confirmation) {
                    // Necesita confirmación del usuario
                    pendingCurrencyChange = currencyCode;
                    pendingButton = button;
                    pendingButtonOriginalText = originalText;

                    // Mostrar información del cupón en el modal
                    document.getElementById('cuponCodigo').textContent = `Cupón: ${data.cupon_info.codigo}`;
                    document.getElementById('cuponDescuento').textContent = `Descuento: ${data.cupon_info.descuento}`;
                    document.getElementById('cuponMensajePerdida').innerHTML =
                        `Este cupón solo es válido para compras en <strong>${data.cupon_info.moneda_original}</strong>.<br>
                        Al cambiar a <strong>${data.new_currency.nombre} (${data.new_currency.simbolo})</strong>, perderás este descuento.`;

                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('confirmarCambioMonedaModal'));
                    modal.show();

                    // Restaurar botón
                    button.innerHTML = originalText;
                    button.disabled = false;
                } else {
                    mostrarNotificacion(data.message || 'Error al cambiar la moneda', 'danger');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error al cambiar la moneda', 'danger');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        // Confirmar cambio de moneda (con pérdida del cupón)
        function confirmarCambioMoneda() {
            if (!pendingCurrencyChange) return;

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmarCambioMonedaModal'));
            modal.hide();

            // Mostrar loading en el botón original
            if (pendingButton) {
                pendingButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                pendingButton.disabled = true;
            }

            // Enviar solicitud con confirmación
            fetch('/cambiar-moneda', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    currency: pendingCurrencyChange,
                    confirmar: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion(data.message, 'warning');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    mostrarNotificacion(data.message || 'Error al cambiar la moneda', 'danger');
                    if (pendingButton) {
                        pendingButton.innerHTML = pendingButtonOriginalText;
                        pendingButton.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarNotificacion('Error al cambiar la moneda', 'danger');
                if (pendingButton) {
                    pendingButton.innerHTML = pendingButtonOriginalText;
                    pendingButton.disabled = false;
                }
            })
            .finally(() => {
                pendingCurrencyChange = null;
                pendingButton = null;
                pendingButtonOriginalText = '';
            });
        }

        // Cancelar cambio de moneda
        function cancelarCambioMoneda() {
            // Limpiar variables pendientes
            pendingCurrencyChange = null;
            if (pendingButton) {
                pendingButton.innerHTML = pendingButtonOriginalText;
                pendingButton.disabled = false;
            }
            pendingButton = null;
            pendingButtonOriginalText = '';

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmarCambioMonedaModal'));
            if (modal) modal.hide();
        }

        // Función para mostrar notificaciones flotantes
        function mostrarNotificacion(mensaje, tipo = 'success') {
            const existingNotification = document.querySelector('.custom-notification');
            if (existingNotification) existingNotification.remove();

            const notification = document.createElement('div');
            notification.className = `custom-notification alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.style.maxWidth = '500px';
            notification.style.backgroundColor = tipo === 'warning' ? '#fff3cd' : (tipo === 'danger' ? '#f8d7da' : '#d1e7dd');
            notification.style.borderLeft = `4px solid ${tipo === 'warning' ? '#ffc107' : (tipo === 'danger' ? '#dc3545' : '#0f5132')}`;
            notification.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';

            const icon = tipo === 'warning' ? 'exclamation-triangle' : (tipo === 'danger' ? 'times-circle' : 'check-circle');

            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icon} me-2 ${tipo === 'warning' ? 'text-warning' : (tipo === 'danger' ? 'text-danger' : 'text-success')}"></i>
                    <div class="flex-grow-1">
                        <strong>${tipo === 'warning' ? '¡Atención!' : (tipo === 'danger' ? 'Error' : 'Éxito')}</strong>
                        <div class="small">${mensaje}</div>
                    </div>
                    <button type="button" class="btn-close btn-sm" onclick="this.closest('.custom-notification').remove()"></button>
                </div>
            `;

            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), tipo === 'warning' ? 5000 : 3000);
        }

        // Event listeners para los botones del modal
        document.addEventListener('DOMContentLoaded', function() {
            const confirmarBtn = document.getElementById('confirmarCambioBtn');
            const cancelarBtn = document.getElementById('cancelarCambioBtn');

            if (confirmarBtn) {
                confirmarBtn.addEventListener('click', confirmarCambioMoneda);
            }
            if (cancelarBtn) {
                cancelarBtn.addEventListener('click', cancelarCambioMoneda);
            }
        });

        // Cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>
