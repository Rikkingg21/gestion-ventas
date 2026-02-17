<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Ventas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info span {
            color: #ecf0f1;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .welcome-card h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .user-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .user-details p {
            margin-bottom: 0.5rem;
            color: #555;
        }

        .user-details strong {
            color: #2c3e50;
            width: 120px;
            display: inline-block;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 2rem;
        }

        .menu-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .menu-card h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .menu-card p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .menu-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Sistema de Gestión de Ventas</h1>
        <div class="user-info">
            <span>Bienvenido, {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h2>Panel de Control</h2>
            <p>Bienvenido al sistema de gestión de ventas. Aquí podrás administrar todas las operaciones de tu negocio.</p>

            <div class="user-details">
                <h3>Tus datos:</h3>
                <p><strong>Nombre:</strong> {{ auth()->user()->name }}</p>
                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                <p><strong>Documento:</strong> {{ auth()->user()->tipo_documento }}: {{ auth()->user()->nro_documento }}</p>
                <p><strong>Teléfono:</strong> {{ auth()->user()->telefono ?? 'No registrado' }}</p>
            </div>
        </div>

        <div class="menu-grid">
            <a href="#" class="menu-card">
                <div class="menu-icon">📦</div>
                <h3>Productos</h3>
                <p>Gestiona tu inventario de productos</p>
            </a>

            <a href="#" class="menu-card">
                <div class="menu-icon">👥</div>
                <h3>Clientes</h3>
                <p>Administra tus clientes</p>
            </a>

            <a href="#" class="menu-card">
                <div class="menu-icon">💰</div>
                <h3>Ventas</h3>
                <p>Registra nuevas ventas</p>
            </a>

            <a href="#" class="menu-card">
                <div class="menu-icon">📊</div>
                <h3>Reportes</h3>
                <p>Visualiza estadísticas y reportes</p>
            </a>
        </div>
    </div>
</body>
</html>
