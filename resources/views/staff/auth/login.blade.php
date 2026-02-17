<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Staff</title>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Panel de Staff</h1>

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

            <form method="POST" action="{{ route('staff.login') }}">
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
