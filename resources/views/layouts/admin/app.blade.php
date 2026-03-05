<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Administrador')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100">
    @php
        $currentUser = Auth::guard('admin')->user();
        $isSuperAdmin = $currentUser && $currentUser->isSuperAdmin();
        $guard = 'admin'; // Guard actual
        $prefix = 'admin'; // Prefijo de la URL
    @endphp

    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-64 bg-indigo-800 text-white flex flex-col">
            <!-- Logo / Título -->
            <div class="p-4 text-2xl font-bold border-b border-indigo-700">
                <i class="fas fa-cogs mr-2"></i>
                Admin Panel
            </div>

            <!-- Módulos de navegación -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-2 px-4">
                    <!-- Dashboard siempre visible para todos -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-indigo-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700' : '' }}">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Módulos dinámicos según permisos -->
                    @if(isset($menuModules) && $menuModules->count() > 0)
                        @foreach($menuModules as $module)
                            @php
                                // Verificar si el usuario tiene permiso de lectura para este módulo
                                $canRead = $isSuperAdmin || ($currentUser && $currentUser->canRead($module->id));

                                // Construir la URL completa según el guard
                                $moduleUrl = $module->route
                                    ? url($prefix . '/' . ltrim($module->route, '/'))
                                    : '#';

                                // Verificar si la URL actual coincide con la ruta del módulo
                                $isActive = request()->is($prefix . '/' . ltrim($module->route, '/'));
                            @endphp

                            @if($canRead || $module->children->isNotEmpty())
                                @if($module->children->isNotEmpty())
                                    <!-- Módulo con submódulos -->
                                    <li x-data="{
                                        open: {{
                                            $isActive ||
                                            $module->children->contains(function($child) use ($prefix) {
                                                return request()->is($prefix . '/' . ltrim($child->route, '/'));
                                            }) ? 'true' : 'false'
                                        }}
                                    }">
                                        <button @click="open = !open"
                                                class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                            <div class="flex items-center space-x-3">
                                                <i class="fas {{ $module->icon ?: 'fa-folder' }} w-5"></i>
                                                <span>{{ $module->name }}</span>
                                            </div>
                                            <i class="fas fa-chevron-down text-xs transition-transform"
                                            :class="{ 'transform rotate-180': open }"></i>
                                        </button>

                                        <!-- Submódulos -->
                                        <ul x-show="open"
                                            @click.away="open = false"
                                            class="ml-8 mt-2 space-y-2"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100">
                                            @foreach($module->children as $child)
                                                @php
                                                    $canReadChild = $isSuperAdmin || ($currentUser && $currentUser->canRead($child->id));
                                                    $childUrl = $child->route
                                                        ? url($prefix . '/' . ltrim($child->route, '/'))
                                                        : '#';
                                                    $isChildActive = request()->is($prefix . '/' . ltrim($child->route, '/'));
                                                @endphp
                                                @if($canReadChild)
                                                    <li>
                                                        <a href="{{ $childUrl }}"
                                                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm {{ $isChildActive ? 'bg-indigo-700' : '' }}">
                                                            <i class="fas {{ $child->icon ?: 'fa-circle' }} w-4 text-xs"></i>
                                                            <span>{{ $child->name }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @elseif($canRead)
                                    <!-- Módulo simple -->
                                    <li>
                                        <a href="{{ $moduleUrl }}"
                                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-indigo-700 transition-colors {{ $isActive ? 'bg-indigo-700' : '' }}">
                                            <i class="fas {{ $module->icon ?: 'fa-circle' }} w-5"></i>
                                            <span>{{ $module->name }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endforeach
                    @else
                        <!-- Mensaje cuando no hay módulos -->
                        <li class="text-indigo-200 text-sm p-2">
                            <i class="fas fa-info-circle mr-2"></i>
                            No hay módulos disponibles
                        </li>
                    @endif
                </ul>
            </nav>

            <!-- Información del usuario en sidebar -->
            <div class="p-4 border-t border-indigo-700">
                <div class="flex items-center space-x-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->nombreCompleto ?? $currentUser->username ?? 'Admin') }}&background=4f46e5&color=fff"
                        alt="Avatar"
                        class="w-8 h-8 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">
                            {{ $currentUser->nombreCompleto ?? $currentUser->username ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-indigo-200 truncate">
                            @if($isSuperAdmin)
                                <i class="fas fa-crown mr-1"></i> Super Admin
                            @else
                                {{ $currentUser->admin->nivel ?? 'Administrador' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar superior -->
            <header class="bg-white shadow-sm">
                <div class="px-6 py-3 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>

                    <div class="flex items-center space-x-4">
                        <button class="relative text-gray-600 hover:text-gray-800">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                                3
                            </span>
                        </button>

                        <!-- Menú de usuario -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($currentUser->nombres ?? 'Admin') }}&background=4f46e5&color=fff"
                                     alt="Avatar"
                                     class="w-8 h-8 rounded-full">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $currentUser->username ?? 'Admin' }}
                                </span>
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>

                            <div x-show="open" @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i> Mi Perfil
                                </a>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 bg-gray-100">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>
