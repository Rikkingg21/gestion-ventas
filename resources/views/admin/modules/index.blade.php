<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulos - Administración</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

</head>
<body class="admin-login">
    <div class="modules-container">
        <div class="header-actions">
            <h1 class="page-title">Gestión de Módulos y Permisos</h1>
            <div>
                <a href="" class="btn btn-primary">
                    + Nuevo Módulo
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="filter-bar">
            <input type="text" class="search-box" placeholder="Buscar módulos..." id="searchModules">
            <select class="search-box" id="filterStatus">
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>

        @forelse($modules as $module)
            <div class="module-card" data-module-id="{{ $module->id }}">
                <div class="module-header" onclick="toggleModule({{ $module->id }})">
                    <div class="module-title">
                        <span class="module-icon">{{ $module->icon ?: '📦' }}</span>
                        <span class="module-name">{{ $module->name }}</span>
                        <span class="module-slug">({{ $module->slug }})</span>
                        @if($module->parent_id)
                            <span class="module-badge">Submódulo</span>
                        @endif
                    </div>
                    <div class="module-status">
                        <span class="status-badge {{ $module->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $module->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                        <div class="module-actions" onclick="event.stopPropagation()">
                            <a href="" class="btn-edit" style="padding: 5px 10px;">Editar</a>
                            <form action="" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete" style="padding: 5px 10px;" onclick="return confirm('¿Eliminar este módulo?')">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="submodules" id="module-{{ $module->id }}" style="display: none;">
                    <!-- Información del módulo -->
                    <div style="margin-bottom: 15px; padding: 10px; background: #e9ecef; border-radius: 5px;">
                        <p><strong>Ruta:</strong> {{ $module->route ?: 'No definida' }}</p>
                        <p><strong>Orden:</strong> {{ $module->order_position }}</p>
                        <p><strong>Descripción:</strong> {{ $module->description ?: 'Sin descripción' }}</p>
                    </div>

                    <!-- Permisos del módulo -->
                    <div class="permissions-list">
                        <h4 style="margin-bottom: 10px;">Permisos del Módulo</h4>
                        @forelse($module->permissions as $permission)
                            @php
                                $action = explode('.', $permission->slug)[1] ?? 'view';
                            @endphp
                            <span class="permission-tag {{ $action }}">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <p style="color: #6c757d;">No hay permisos definidos</p>
                        @endforelse

                        <div style="margin-top: 15px;">
                            <a href="" class="btn-primary" style="padding: 5px 10px; font-size: 12px;">
                                + Agregar Permiso
                            </a>
                        </div>
                    </div>

                    <!-- Submódulos -->
                    @if($module->children->count() > 0)
                        <h4 style="margin: 20px 0 10px;">Submódulos</h4>
                        @foreach($module->children as $submodule)
                            <div class="submodule-item">
                                <div class="submodule-info">
                                    <span class="module-icon" style="font-size: 18px;">📁</span>
                                    <span class="submodule-name">{{ $submodule->name }}</span>
                                    <span class="submodule-route">{{ $submodule->route }}</span>
                                </div>
                                <div class="module-actions">
                                    <a href="" style="color: #28a745;">✏️</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <h3>No hay módulos creados</h3>
                <a href="" class="btn btn-primary">
                    Crear Primer Módulo
                </a>
            </div>
        @endforelse
    </div>

    <script>
        function toggleModule(moduleId) {
            const moduleContent = document.getElementById(`module-${moduleId}`);
            const allContents = document.querySelectorAll('[id^="module-"]');

            // Cerrar otros abiertos
            allContents.forEach(content => {
                if (content.id !== `module-${moduleId}`) {
                    content.style.display = 'none';
                }
            });

            // Toggle actual
            moduleContent.style.display = moduleContent.style.display === 'none' ? 'block' : 'none';
        }

        // Búsqueda de módulos
        document.getElementById('searchModules').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const modules = document.querySelectorAll('.module-card');

            modules.forEach(module => {
                const moduleName = module.querySelector('.module-name').textContent.toLowerCase();
                const moduleSlug = module.querySelector('.module-slug').textContent.toLowerCase();

                if (moduleName.includes(searchTerm) || moduleSlug.includes(searchTerm)) {
                    module.style.display = 'block';
                } else {
                    module.style.display = 'none';
                }
            });
        });

        // Filtro por estado
        document.getElementById('filterStatus').addEventListener('change', function() {
            const filter = this.value;
            const modules = document.querySelectorAll('.module-card');

            modules.forEach(module => {
                const statusBadge = module.querySelector('.status-badge');
                const isActive = statusBadge.classList.contains('status-active');

                if (filter === '') {
                    module.style.display = 'block';
                } else if (filter === 'active' && isActive) {
                    module.style.display = 'block';
                } else if (filter === 'inactive' && !isActive) {
                    module.style.display = 'block';
                } else {
                    module.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
