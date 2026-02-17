<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .login-box {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
            font-weight: 500;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #090;
            border: 1px solid #cfc;
        }

        .alert-info {
            background-color: #e6f3ff;
            color: #0066cc;
            border: 1px solid #b8daff;
        }

        .alert ul {
            margin-left: 20px;
        }

        .back-link {
            text-align: center;
            margin-bottom: 25px;
        }

        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            color: #333;
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0052a3;
        }

        button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0,102,204,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Panel de Administración</h1>

            <!-- Mensajes de error -->
            @if($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('mensaje'))
                <div class="alert alert-info">
                    {{ session('mensaje') }}
                </div>
            @endif

            <!-- Enlace a la tienda -->
            <div class="back-link">
                <a href="/">← Volver a la tienda</a>
            </div>

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Recordarme</label>
                </div>

                <button type="submit">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
