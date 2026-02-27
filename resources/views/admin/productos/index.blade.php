@extends('layouts.admin.app')

@section('title', 'Productos')
@section('page-title', 'Gestión de Productos')

@section('content')
<div class="bg-white rounded-lg shadow-lg">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Listado de Productos</h3>
            <a href="{{ route('admin.productos.create') }}" class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-4 py-2 rounded-lg hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i>Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Filtros mejorados -->
    <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <input type="text" name="search" placeholder="Buscar productos..." value="{{ request('search') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <select name="categoria" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>

            <select name="tipo" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Todos los tipos</option>
                <option value="fisico" {{ request('tipo') == 'fisico' ? 'selected' : '' }}>Físico</option>
                <option value="digital" {{ request('tipo') == 'digital' ? 'selected' : '' }}>Digital</option>
            </select>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 shadow-sm hover:shadow flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>

                @if(request()->anyFilled(['search', 'categoria', 'tipo']))
                    <a href="{{ route('admin.productos.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors duration-200 flex items-center">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla mejorada -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 uppercase text-xs font-semibold tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left rounded-tl-lg">Imagen</th>
                    <th class="px-6 py-3 text-left">Nombre / SKU</th>
                    <th class="px-6 py-3 text-left">Categoría</th>
                    <th class="px-6 py-3 text-left">Tipo</th>
                    <th class="px-6 py-3 text-left">Precios</th>
                    <th class="px-6 py-3 text-left">Stock</th>
                    <th class="px-6 py-3 text-left">Estado</th>
                    <th class="px-6 py-3 text-right rounded-tr-lg">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($productos as $producto)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4">
                        @php
                            $imagenPrincipal = null;
                            for ($i = 1; $i <= 5; $i++) {
                                $campo = 'imagen_url_' . $i;
                                if ($producto->$campo) {
                                    $imagenPrincipal = $producto->getImageUrl($campo);
                                    break;
                                }
                            }
                        @endphp

                        @if($imagenPrincipal)
                            <div class="relative group">
                                <img src="{{ $imagenPrincipal }}" alt="{{ $producto->nombre }}"
                                     class="w-14 h-14 object-cover rounded-lg shadow-md group-hover:shadow-lg transition-all duration-200 group-hover:scale-110 cursor-pointer"
                                     onclick="openImageModal('{{ $imagenPrincipal }}', '{{ $producto->nombre }}')">
                                @php
                                    $totalImagenes = 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($producto->{'imagen_url_' . $i}) $totalImagenes++;
                                    }
                                @endphp
                                @if($totalImagenes > 1)
                                    <span class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center shadow-md">
                                        {{ $totalImagenes }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="w-14 h-14 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center shadow-inner">
                                <i class="fas fa-image text-gray-400 text-xl"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $producto->nombre }}</div>
                        @if($producto->sku)
                            <div class="text-xs text-gray-500 mt-1">
                                <span class="font-semibold">SKU:</span> {{ $producto->sku }}
                            </div>
                        @endif
                        @if($producto->tipo_producto == 'digital' && $producto->url_recurso)
                            <div class="text-xs text-gray-500 mt-1 truncate max-w-xs" title="{{ $producto->url_recurso }}">
                                <i class="fas fa-link mr-1"></i> {{ Str::limit($producto->url_recurso, 30) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-medium">
                            {{ $producto->categoria->nombre }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 text-xs rounded-full font-medium {{ $producto->tipo_producto == 'digital' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            <i class="fas fa-{{ $producto->tipo_producto == 'digital' ? 'cloud' : 'box' }} mr-1"></i>
                            {{ ucfirst($producto->tipo_producto) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm space-y-1">
                            @forelse($producto->precios as $precio)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">
                                        {{ $precio->moneda->simbolo }} {{ number_format($precio->precio, 2) }}
                                        @if($producto->aplica_descuento && $producto->porcentaje_descuento)
                                            <span class="ml-1 text-xs text-green-600 font-normal">
                                                (-{{ $producto->porcentaje_descuento }}%)
                                            </span>
                                            <div class="text-xs text-gray-500">
                                                → {{ $precio->moneda->simbolo }} {{ number_format($precio->precio * (1 - $producto->porcentaje_descuento/100), 2) }}
                                            </div>
                                        @endif
                                    </span>
                                    <span class="text-xs text-gray-500 ml-2">{{ $precio->moneda->codigo_iso }}</span>
                                </div>
                            @empty
                                <span class="text-gray-400">Sin precio</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($producto->tipo_producto == 'fisico')
                            @php
                                $stockActual = $producto->getStockActualAttribute();
                                $stockMinimo = optional($producto->stock)->stock_minimo ?? 0;

                                if ($stockActual === 0) {
                                    $stockClass = 'text-red-600 bg-red-50';
                                    $stockIcon = 'times-circle';
                                } elseif ($stockActual <= $stockMinimo) {
                                    $stockClass = 'text-yellow-600 bg-yellow-50';
                                    $stockIcon = 'exclamation-triangle';
                                } else {
                                    $stockClass = 'text-green-600 bg-green-50';
                                    $stockIcon = 'check-circle';
                                }
                            @endphp
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $stockClass }}">
                                    <i class="fas fa-{{ $stockIcon }} mr-1"></i>
                                    {{ $stockActual ?? 0 }} unid.
                                </span>
                                @if($stockMinimo > 0)
                                    <span class="text-xs text-gray-500" title="Stock mínimo">
                                        (mín: {{ $stockMinimo }})
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400 flex items-center text-sm">
                                <i class="fas fa-infinity mr-1"></i> Ilimitado
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 inline-flex items-center text-xs rounded-full font-medium {{ $producto->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <span class="w-2 h-2 rounded-full mr-1.5 {{ $producto->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $producto->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="{{ route('admin.productos.edit', $producto->id) }}"
                           class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200"
                           title="Editar producto">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.productos.destroy', $producto->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmDelete(this, '{{ $producto->nombre }}')"
                                    class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                    title="Eliminar producto">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <i class="fas fa-box-open text-5xl mb-4"></i>
                            <p class="text-lg font-medium">No hay productos disponibles</p>
                            <p class="text-sm mt-2">Comienza creando un nuevo producto</p>
                            <a href="{{ route('admin.productos.create') }}" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Crear Producto
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación mejorada -->
    <div class="p-6 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <div class="text-sm text-gray-600 mb-4 sm:mb-0">
                Mostrando {{ $productos->firstItem() ?? 0 }} - {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }} productos
            </div>
            <div class="flex justify-center">
                {{ $productos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver imagen ampliada -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-screen p-4">
        <img id="modalImage" src="" alt="" class="max-w-full max-h-screen rounded-lg shadow-2xl">
    </div>
    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-75 transition-all">
        <i class="fas fa-times text-2xl"></i>
    </button>
</div>

<script>
function confirmDelete(button, productName = 'este producto') {
    Swal.fire({
        title: '¿Estás seguro?',
        html: `Estás a punto de eliminar <strong>${productName}</strong><br>Esta acción no se puede revertir.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('form').submit();
        }
    });
}

function openImageModal(imageUrl, productName) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageUrl;
    modalImage.alt = productName;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<!-- Estilos adicionales para la paginación -->
<style>
    .pagination {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .pagination .page-item {
        list-style: none;
    }
    .pagination .page-link {
        display: block;
        padding: 0.5rem 0.75rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        color: #4b5563;
        transition: all 0.2s;
    }
    .pagination .page-link:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .pagination .active .page-link {
        background: #4f46e5;
        border-color: #4f46e5;
        color: white;
    }
    .pagination .disabled .page-link {
        opacity: 0.5;
        pointer-events: none;
    }
</style>
@endsection
