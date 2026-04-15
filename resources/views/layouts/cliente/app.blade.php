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


</head>
<body class="bg-light" style="min-height: 100vh; display: flex; flex-direction: column;">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand fw-bold text-primary" href="{{ route('home') }}">
                <i class="fas fa-building me-2"></i>
                Mi Empresa
            </a>

            <!-- Toggler -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Collapse -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Menú principal -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active text-primary fw-semibold' : 'text-dark' }}"
                           href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>

                    <!-- Módulos Públicos -->
                    @if(isset($publicModules) && $publicModules->count() > 0)
                        @foreach($publicModules as $module)
                            @if($module->children->count() > 0)
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-dark" href="#"
                                       id="navbarDropdown{{ $module->id }}"
                                       role="button" data-bs-toggle="dropdown">
                                        <i class="fas {{ $module->icon ?: 'fa-cube' }} me-1"></i>
                                        {{ $module->name }}
                                    </a>
                                    <ul class="dropdown-menu border-0 shadow-sm">
                                        @foreach($module->children as $child)
                                            <li>
                                                <a class="dropdown-item py-2" href="{{ url($child->route) }}">
                                                    <i class="fas {{ $child->icon ?: 'fa-circle' }} fa-xs me-2 text-primary"></i>
                                                    {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link text-dark" href="{{ url($module->route) }}">
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
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="navbarDropdownMenu"
                               role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-cog me-1"></i>Mi Panel
                            </a>
                            <ul class="dropdown-menu border-0 shadow-sm">
                                @foreach($menuModules as $module)
                                    @if($module->children->count() > 0)
                                        <li class="dropdown-submenu position-relative">
                                            <a class="dropdown-item dropdown-toggle py-2" href="#">
                                                <i class="fas {{ $module->icon ?: 'fa-folder' }} me-2 text-primary"></i>
                                                {{ $module->name }}
                                            </a>
                                            <ul class="dropdown-menu border-0 shadow-sm">
                                                @foreach($module->children as $child)
                                                    <li>
                                                        <a class="dropdown-item py-2" href="{{ url($child->route) }}">
                                                            <i class="fas {{ $child->icon ?: 'fa-circle' }} fa-xs me-2 text-primary"></i>
                                                            {{ $child->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @else
                                        <li>
                                            <a class="dropdown-item py-2" href="{{ url($module->route) }}">
                                                <i class="fas {{ $module->icon ?: 'fa-circle' }} me-2 text-primary"></i>
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
                @if(isset($userGeoInfo) && isset($userGeoInfo['country']) && isset($userGeoInfo['currency']))
                <div class="dropdown me-2">
                    <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        @if(isset($userGeoInfo['country']['code']) && $userGeoInfo['country']['code'] !== 'INT')
                            <span class="flag-icon flag-icon-{{ strtolower($userGeoInfo['country']['code']) }}"></span>
                        @else
                            <span class="flag-icon flag-icon-un"></span>
                        @endif
                        <span class="d-none d-lg-inline">
                            {{ $userGeoInfo['currency']['codigo'] ?? 'USD' }}
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-3" style="min-width: 320px;">
                        <li>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                @if(isset($userGeoInfo['country']['code']) && $userGeoInfo['country']['code'] !== 'INT')
                                    <span class="flag-icon flag-icon-{{ strtolower($userGeoInfo['country']['code']) }}" style="font-size: 2rem;"></span>
                                @else
                                    <span class="flag-icon flag-icon-un" style="font-size: 2rem;"></span>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $userGeoInfo['country']['name'] ?? 'Internacional' }}</h6>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="mb-2 fw-semibold">Moneda actual:</div>
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3">
                                <div>
                                    <div class="fw-bold">{{ $userGeoInfo['currency']['nombre'] ?? 'Dólar Americano' }}</div>
                                    <small class="text-muted">{{ $userGeoInfo['currency']['codigo'] ?? 'USD' }}</small>
                                </div>
                                <div class="h3 mb-0 text-primary">{{ $userGeoInfo['currency']['simbolo'] ?? '$' }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="mb-2 fw-semibold">Cambiar moneda:</div>
                            <div class="row g-2">
                                @forelse($monedasDisponibles as $moneda)
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm w-100
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
                        </li>
                    </ul>
                </div>
                @endif

                <!-- Menú de usuario -->
                @if(isset($isAuthenticated) && $isAuthenticated)
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                    id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->nombres ?? 'Usuario') }}&background=0d6efd&color=fff"
                            alt="Avatar"
                            class="user-avatar me-2" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                        <span class="d-none d-lg-inline">{{ $currentUser->nombres ?? 'Mi Cuenta' }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('client.profile.edit') }}">
                                <i class="fas fa-user me-2 text-primary"></i>Mi Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('client.logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @else
                <div class="d-flex gap-2">
                    <a href="{{ route('client.login') }}" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </a>
                    <a href="{{ route('client.register') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Registrarse
                    </a>
                </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Modal de confirmación para cambio de moneda -->
    <div class="modal fade" id="confirmarCambioMonedaModal" tabindex="-1" aria-labelledby="confirmarCambioMonedaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
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

    <!-- Contenido principal -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer py-4 mt-auto bg-light border-top" style="padingpadding: 20px;">
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
