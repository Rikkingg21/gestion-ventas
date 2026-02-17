<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bienvenido</title>
</head>
<body>
    <h1>Hola Cliente</h1>
    <form method="POST" action="{{ route('client.logout') }}">
        @csrf
        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
            Cerrar Sesión
        </button>
    </form>
</body>
</html>
