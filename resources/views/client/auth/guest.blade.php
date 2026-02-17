<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
            <!-- Logo o título -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">¡Bienvenido!</h1>
                <p class="text-gray-600 mt-2">Acceso al área de clientes</p>
            </div>

            <!-- Mensaje principal -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            No estás logueado en el sistema. Para acceder al dashboard de cliente necesitas iniciar sesión.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="space-y-3">
                <a href="{{ route('client.login') }}"
                   class="block w-full text-center bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition duration-300">
                    Iniciar Sesión
                </a>

                <a href="{{ route('client.register') }}"
                   class="block w-full text-center bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition duration-300">
                    Registrarme como Cliente
                </a>

                <a href="{{ route('home') }}"
                   class="block w-full text-center bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition duration-300">
                    Visitar nuestra Tienda
                </a>
            </div>

            <!-- Mensaje adicional -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>¿Ya tienes una cuenta? <a href="{{ route('client.login') }}" class="text-blue-500 hover:underline">Inicia sesión</a></p>
                <p class="mt-2">¿No eres cliente? <a href="{{ route('home') }}" class="text-blue-500 hover:underline">Explora nuestros productos</a></p>
            </div>
        </div>
    </div>
</body>
</html>
