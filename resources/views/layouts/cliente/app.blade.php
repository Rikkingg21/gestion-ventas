<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
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

                <!-- Menú de usuario / Login -->
                <ul class="navbar-nav">
                    @if(isset($isAuthenticated) && $isAuthenticated)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                               id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->nombre ?? 'Usuario') }}&background=065f46&color=fff"
                                     alt="Avatar"
                                     class="user-avatar me-2">
                                <span>{{ $currentUser->nombre ?? 'Mi Cuenta' }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('client.profile.edit') }}">
                                        <i class="fas fa-user me-2"></i>Mi Perfil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('client.logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
