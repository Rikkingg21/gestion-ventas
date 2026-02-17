<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Módulo</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: auto;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .icon-selector {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .icon-option {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .icon-option:hover {
            background: #f0f0f0;
            border-color: var(--color-primary);
        }

        .icon-option.selected {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }

        .icon-option span {
            display: block;
            font-size: 24px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="admin-login">
    <div class="form-container">
        <h1 class="form-title">Crear Nuevo Módulo</h1>

        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.modules.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nombre del Módulo *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                <div class="help-text">Ej: Usuarios, Ventas, Productos</div>
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required>
                <div class="help-text">Identificador único. Ej: users, sales, products</div>
            </div>

            <div class="form-group">
                <label for="icon">Icono</label>
                <input type="text" id="icon" name="icon" value="{{ old('icon', '📦') }}">
                <div class="help-text">Puedes usar emojis o clases de iconos (ej: bi-people, fa-users)</div>

                <div class="icon-selector">
                    <div class="icon-option" onclick="selectIcon('📦')">
                        <span>📦</span>
                        <small>Módulo</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('👥')">
                        <span>👥</span>
                        <small>Usuarios</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('💰')">
                        <span>💰</span>
                        <small>Ventas</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('📊')">
                        <span>📊</span>
                        <small>Reportes</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('⚙️')">
                        <span>⚙️</span>
                        <small>Config</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('🏠')">
                        <span>🏠</span>
                        <small>Dashboard</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('📁')">
                        <span>📁</span>
                        <small>Archivos</small>
                    </div>
                    <div class="icon-option" onclick="selectIcon('🔒')">
                        <span>🔒</span>
                        <small>Seguridad</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="parent_id">Módulo Padre</label>
                <select id="parent_id" name="parent_id">
                    <option value="">-- Ninguno (Módulo Principal) --</option>
                    @foreach($parentModules ?? [] as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
                <div class="help-text">Si es un submódulo, selecciona su padre</div>
            </div>

            <div class="form-group">
                <label for="route">Ruta</label>
                <input type="text" id="route" name="route" value="{{ old('route') }}">
                <div class="help-text">Ej: admin.users.index, client.products</div>
            </div>

            <div class="form-group">
                <label for="order_position">Orden</label>
                <input type="number" id="order_position" name="order_position" value="{{ old('order_position', 0) }}" min="0">
                <div class="help-text">Número para ordenar los módulos (menor = primero)</div>
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active">Módulo Activo</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Módulo</button>
                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        function selectIcon(icon) {
            document.getElementById('icon').value = icon;

            // Marcar el seleccionado
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }

        // Generar slug automáticamente desde el nombre
        document.getElementById('name').addEventListener('keyup', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');

            document.getElementById('slug').value = slug;
        });
    </script>
</body>
</html>
