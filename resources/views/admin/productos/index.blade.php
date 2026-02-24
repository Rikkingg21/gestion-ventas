@extends('layouts.admin.app')

@section('title', 'Productos')
@section('page-title', 'Gestión de Productos')

@section('content')
<div class="bg-white rounded-lg shadow-lg">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold">Listado de Productos</h3>
            <a href="{{ route('admin.productos.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}"
                   class="border rounded-lg px-4 py-2">

            <select name="categoria" class="border rounded-lg px-4 py-2">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="tipo" class="border rounded-lg px-4 py-2">
                <option value="">Todos los tipos</option>
                <option value="fisico" {{ request('tipo') == 'fisico' ? 'selected' : '' }}>Físico</option>
                <option value="digital" {{ request('tipo') == 'digital' ? 'selected' : '' }}>Digital</option>
            </select>

            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
        </form>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">Imagen</th>
                    <th class="px-6 py-3 text-left">Nombre</th>
                    <th class="px-6 py-3 text-left">Categoría</th>
                    <th class="px-6 py-3 text-left">Tipo</th>
                    <th class="px-6 py-3 text-left">Precio USD</th>
                    <th class="px-6 py-3 text-left">Precio Local</th>
                    <th class="px-6 py-3 text-left">Stock</th>
                    <th class="px-6 py-3 text-left">Estado</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-6 py-4">
                        @php
                            $imagenPrincipal = null;
                            for ($i = 1; $i <= 5; $i++) {
                                $campo = 'imagen_url_' . $i;
                                if ($producto->$campo) {
                                    $imagenPrincipal = $producto->$campo;
                                    break;
                                }
                            }
                        @endphp

                        @if($imagenPrincipal)
                            <img src="{{ asset($imagenPrincipal) }}" alt="{{ $producto->nombre }}"
                                 class="w-12 h-12 object-cover rounded-lg">
                        @else
                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-medium">{{ $producto->nombre }}</td>
                    <td class="px-6 py-4">{{ $producto->categoria->nombre }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $producto->tipo_producto == 'digital' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($producto->tipo_producto) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">${{ number_format($producto->precioUSD, 2) }}</td>
                    <td class="px-6 py-4">S/ {{ number_format($producto->precioLocal, 2) }}</td>
                    <td class="px-6 py-4">
                        @if($producto->tipo_producto == 'fisico')
                            <span class="{{ optional($producto->stock)->cantidad > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ optional($producto->stock)->cantidad ?? 0 }} unidades
                            </span>
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $producto->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $producto->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.productos.edit', $producto->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.productos.destroy', $producto->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDelete(this)" class="text-red-600 hover:text-red-900" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-6">
        {{ $productos->links() }}
    </div>
</div>

<script>
function confirmDelete(button) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('form').submit();
        }
    });
}
</script>
@endsection
