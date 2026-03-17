<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Empresa - Clientes')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #065f46;
            --primary-hover: #047857;
            --bg-light: #f3f4f6;
        }

        body {
            background-color: var(--bg-light);
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
    </style>

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navbar con módulos -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-building me-2"></i>
                Mi Empresa
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <!-- Módulos Públicos en el navbar -->
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

                    <!-- Módulos con permisos (solo para usuarios autenticados) -->
                    @if(isset($isAuthenticated) && $isAuthenticated && isset($menuModules) && $menuModules->count() > 0)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenu"
                               role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-cog me-1"></i>Mi Panel
                            </a>
                            <ul class="dropdown-menu">
                                @foreach($menuModules as $module)
                                    @if($module->children->count() > 0)
                                        <li>
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
                <!-- En el navbar, antes del menú de usuario -->
                <ul class="navbar-nav me-3">
                    <!-- Selector de moneda/pais -->
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
                                {{ $userGeoInfo['currency']['symbol'] ?? '$' }}
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
                                                    <span class="flag-icon flag-icon-{{ strtolower($moneda->pais ?? 'us') }} me-1"></span>
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
                            @if(isset($userGeoInfo['city']) || isset($userGeoInfo['region']))
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <div class="dropdown-item-text">
                                    <small class="text-muted">
                                        <i class="fas fa-location-dot me-1"></i>
                                        Ubicación detectada:
                                        @if(isset($userGeoInfo['city'])) {{ $userGeoInfo['city'] }} @endif
                                        @if(isset($userGeoInfo['region'])) ({{ $userGeoInfo['region'] }}) @endif
                                    </small>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </li>
                    @endif
                </ul>

                <!-- Menú de usuario / Login -->
                <ul class="navbar-nav ms-3">
                    @if(isset($isAuthenticated) && $isAuthenticated)
                        <li class="nav-item dropdown" style="position: relative;">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                            id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->nombres ?? 'Usuario') }}&background=065f46&color=fff"
                                    alt="Avatar"
                                    class="user-avatar me-2">
                                <span>{{ $currentUser->nombres ?? 'Mi Cuenta' }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow"
                                style="z-index: 10000; position: absolute; top: 100%; right: 0; left: auto; min-width: 200px; margin-top: 0.5rem;"
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

    <!-- Contenido principal (cada vista inyectará aquí su contenido) -->
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

    <style>
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

        /* Evitar que se superpongan con el contenido */
        .navbar-nav .dropdown-menu.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Asegurar que los dropdowns estén por encima del contenido principal */
        .sticky-top {
            z-index: 1020 !important;
        }

        .navbar {
            position: relative !important;
            z-index: 1030 !important;
        }

        .nav-item.dropdown {
            position: relative !important;
        }

        /* Ajustar el espaciado entre los dropdowns */
        .navbar-nav {
            align-items: center !important;
        }

        /* Para el contenedor de moneda y usuario */
        .navbar-nav.me-3 {
            margin-right: 1rem !important;
        }

        /* Si los dropdowns se cortan, asegurar que sean visibles */
        .dropdown-menu {
            overflow: visible !important;
            max-height: none !important;
        }

        /* Para pantallas pequeñas */
        @media (max-width: 991.98px) {
            /* En móvil, que ocupen todo el ancho */
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
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/carrito.js') }}"></script>
    <script>
        function cambiarMoneda(currencyCode) {
            // Mostrar indicador de carga (opcional)
            const button = event.target;
            const originalText = button.innerHTML;
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
            .then(async response => {
                const contentType = response.headers.get('content-type');

                // Verificar si la respuesta es JSON
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Respuesta no válida (no es JSON): ${text.substring(0, 100)}...`);
                }

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Error en la respuesta del servidor');
                }

                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Recargar la página después de un pequeño retraso
                    setTimeout(() => {
                        location.reload();
                    }, 300);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo cambiar la moneda'));
                }
            })
            .catch(error => {
                console.error('Error detallado:', error);

                // Mostrar mensaje más descriptivo
                if (error.message.includes('DOCTYPE')) {
                    alert('Error de autenticación o ruta no encontrada. Por favor, inicia sesión nuevamente.');
                } else {
                    alert('Error al cambiar la moneda: ' + error.message);
                }
            })
            .finally(() => {
                // Restaurar el botón
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
