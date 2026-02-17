<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .terms-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0;
        }

        .terms-group input {
            width: auto;
        }

        .terms-group label {
            margin-bottom: 0;
            font-size: 14px;
        }

        .terms-group a {
            color: #667eea;
            text-decoration: none;
        }

        .terms-group a:hover {
            text-decoration: underline;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #5a67d8;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .required-field::after {
            content: " *";
            color: #e74c3c;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Crear Cuenta de Cliente</h1>
        <p class="subtitle">Regístrate para acceder a todos nuestros servicios</p>

        @if($errors->any())
            <div class="alert-error">
                <ul style="margin-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.register') }}">
            @csrf

            <div class="form-group">
                <label for="nombres" class="required-field">Nombres</label>
                <input type="text" id="nombres" name="nombres" value="{{ old('nombres') }}"
                       placeholder="Ej: Juan Carlos" required autofocus>
                @error('nombres')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="apellido_paterno" class="required-field">Apellido Paterno</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno"
                           value="{{ old('apellido_paterno') }}" placeholder="Ej: Pérez" required>
                    @error('apellido_paterno')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="apellido_materno" class="required-field">Apellido Materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno"
                           value="{{ old('apellido_materno') }}" placeholder="Ej: García" required>
                    @error('apellido_materno')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="required-field">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="Ej: juan.perez@email.com" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_documento" class="required-field">Tipo de Documento</label>
                    <select id="tipo_documento" name="tipo_documento" required>
                        <option value="">Seleccionar</option>
                        <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC</option>
                        <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                        <option value="PASAPORTE" {{ old('tipo_documento') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                    </select>
                    @error('tipo_documento')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nro_documento" class="required-field">Número de Documento</label>
                    <input type="text" id="nro_documento" name="nro_documento"
                           value="{{ old('nro_documento') }}" placeholder="Ej: 12345678" required>
                    @error('nro_documento')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono (opcional)</label>
                <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}"
                       placeholder="Ej: 999888777">
                @error('telefono')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="required-field">Contraseña</label>
                    <input type="password" id="password" name="password"
                           placeholder="Mínimo 6 caracteres" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="required-field">Confirmar Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Repite tu contraseña" required>
                </div>
            </div>

            <div class="terms-group">
                <input type="checkbox" id="terminos" name="terminos" value="1"
                       {{ old('terminos') ? 'checked' : '' }} required>
                <label for="terminos">
                    Acepto los <a href="#" target="_blank">Términos y Condiciones</a> y la
                    <a href="#" target="_blank">Política de Privacidad</a>
                </label>
            </div>
            @error('terminos')
                <div class="error" style="margin-bottom: 10px;">{{ $message }}</div>
            @enderror

            <button type="submit">Registrarme como Cliente</button>
        </form>

        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="{{ route('client.login') }}">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>
