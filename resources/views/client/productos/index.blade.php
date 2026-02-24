@extends('layouts.cliente.app')

@section('title', 'Productos - Mi Empresa')
@section('page-title', 'Catálogo de Productos')

@section('content')
<div class="container-fluid py-4">
    <!-- Filtros y búsqueda -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-success active" data-filter="all">
                    <i class="fas fa-th-large me-2"></i>Todos
                </button>
                <button class="btn btn-outline-success" data-filter="fisico">
                    <i class="fas fa-box me-2"></i>Productos Físicos
                </button>
                <button class="btn btn-outline-success" data-filter="digital">
                    <i class="fas fa-cloud-download-alt me-2"></i>Productos Digitales
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-success text-white">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" class="form-control" id="searchProduct" placeholder="Buscar productos...">
            </div>
        </div>
    </div>

    <!-- Productos por Categorías -->
    @forelse($categorias as $categoria)
        @if($categoria->productos->count() > 0)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold text-success">
                            <i class="fas fa-folder me-2"></i>
                            {{ $categoria->nombre }}
                        </h4>
                        @if($categoria->descripcion)
                            <span class="text-secondary small">{{ $categoria->descripcion }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($categoria->productos as $producto)
                            <div class="col-xl-3 col-lg-4 col-md-6 producto-item"
                                 data-tipo="{{ $producto->tipo_producto }}"
                                 data-nombre="{{ strtolower($producto->nombre) }}">
                                <div class="card h-100 shadow-sm product-card border-0">
                                    <!-- Imagen del producto -->
                                    <div class="position-relative">
                                        @if($producto->imagen_url_1)
                                            <img src="{{ $producto->imagen_url_1 }}"
                                                 class="card-img-top"
                                                 alt="{{ $producto->nombre }}"
                                                 style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="height: 200px;">
                                                <i class="fas fa-image fa-4x text-secondary"></i>
                                            </div>
                                        @endif

                                        <!-- Badge de tipo de producto -->
                                        <span class="position-absolute top-0 end-0 m-2 badge
                                            {{ $producto->tipo_producto == 'digital' ? 'bg-info' : 'bg-primary' }}">
                                            <i class="fas {{ $producto->tipo_producto == 'digital' ? 'fa-cloud' : 'fa-box' }} me-1"></i>
                                            {{ ucfirst($producto->tipo_producto) }}
                                        </span>
                                    </div>

                                    <div class="card-body">
                                        <h5 class="card-title fw-bold">{{ $producto->nombre }}</h5>

                                        @if($producto->descripcion)
                                            <p class="card-text text-secondary small">
                                                {{ Str::limit($producto->descripcion, 80) }}
                                            </p>
                                        @endif

                                        <!-- SKU o referencia -->
                                        @if($producto->sku)
                                            <p class="small text-secondary mb-2">
                                                <i class="fas fa-barcode me-1"></i>
                                                SKU: {{ $producto->sku }}
                                            </p>
                                        @endif

                                        <!-- Stock para productos físicos -->
                                        @if($producto->esFisico() && $producto->stock)
                                            @if($producto->stock->cantidad > 0)
                                                <span class="badge bg-success bg-opacity-10 text-white mb-2">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    {{ $producto->stock->cantidad }} disponibles
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger mb-2">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Agotado
                                                </span>
                                            @endif
                                        @endif

                                        <!-- Precios -->
                                        <div class="mt-3">
                                            @if($producto->precioUSD)
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="text-secondary">USD:</span>
                                                    <span class="fw-bold text-success">${{ number_format($producto->precioUSD, 2) }}</span>
                                                </div>
                                            @endif

                                            @if($producto->precioLocal)
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-secondary">Local:</span>
                                                    <span class="fw-bold text-success">${{ number_format($producto->precioLocal, 2) }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Badge de descuento -->
                                        @if($producto->aplica_descuento)
                                            <span class="badge bg-warning text-dark mt-2">
                                                <i class="fas fa-tag me-1"></i>
                                                Aplica descuento
                                            </span>
                                        @endif
                                    </div>

                                    <div class="card-footer bg-white border-0 pb-3">
                                        <div class="d-grid gap-2">
                                            @if($producto->esFisico() && (!$producto->stock || $producto->stock->cantidad == 0))
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="fas fa-times-circle me-2"></i>
                                                    No disponible
                                                </button>
                                            @else
                                                <button class="btn btn-success" onclick="agregarAlCarrito({{ $producto->id }})">
                                                    <i class="fas fa-cart-plus me-2"></i>
                                                    Agregar al carrito
                                                </button>
                                            @endif

                                            <a href="{{ route('producto.detalle', $producto->id) }}" class="btn btn-outline-success">
                                                <i class="fas fa-eye me-2"></i>
                                                Ver detalles
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal para detalles del producto -->
                            <div class="modal fade" id="productoModal{{ $producto->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-box me-2"></i>
                                                {{ $producto->nombre }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @if($producto->imagen_url_1)
                                                        <img src="{{ $producto->imagen_url_1 }}"
                                                             class="img-fluid rounded mb-3"
                                                             alt="{{ $producto->nombre }}">
                                                    @endif

                                                    @if($producto->imagen_url_2 || $producto->imagen_url_3 || $producto->imagen_url_4 || $producto->imagen_url_5)
                                                        <div class="row g-2">
                                                            @for($i = 2; $i <= 5; $i++)
                                                                @php $imgUrl = "imagen_url_$i"; @endphp
                                                                @if($producto->$imgUrl)
                                                                    <div class="col-3">
                                                                        <img src="{{ $producto->$imgUrl }}"
                                                                             class="img-fluid rounded"
                                                                             alt="Imagen {{ $i }}">
                                                                    </div>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold">Categoría:</h6>
                                                    <p>{{ $categoria->nombre }}</p>

                                                    <h6 class="fw-bold mt-3">Descripción:</h6>
                                                    <p>{{ $producto->descripcion ?: 'Sin descripción' }}</p>

                                                    <h6 class="fw-bold mt-3">Tipo:</h6>
                                                    <p>
                                                        <span class="badge {{ $producto->tipo_producto == 'digital' ? 'bg-info' : 'bg-primary' }}">
                                                            {{ ucfirst($producto->tipo_producto) }}
                                                        </span>
                                                    </p>

                                                    @if($producto->sku)
                                                        <h6 class="fw-bold mt-3">SKU:</h6>
                                                        <p>{{ $producto->sku }}</p>
                                                    @endif

                                                    @if($producto->esDigital() && $producto->url_recurso)
                                                        <h6 class="fw-bold mt-3">Recurso digital:</h6>
                                                        <a href="{{ $producto->url_recurso }}" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-external-link-alt me-1"></i>Ver recurso
                                                        </a>
                                                    @endif

                                                    <hr>

                                                    <h5 class="fw-bold text-success">Precios:</h5>
                                                    @if($producto->precioUSD)
                                                        <p class="mb-1"><strong>USD:</strong> ${{ number_format($producto->precioUSD, 2) }}</p>
                                                    @endif
                                                    @if($producto->precioLocal)
                                                        <p><strong>Local:</strong> ${{ number_format($producto->precioLocal, 2) }}</p>
                                                    @endif

                                                    @if($producto->aplica_descuento)
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-tag me-1"></i>Aplica descuento
                                                        </span>
                                                    @endif

                                                    @if($producto->esFisico() && $producto->stock)
                                                        <div class="alert {{ $producto->stock->cantidad > 0 ? 'alert-success' : 'alert-danger' }} mt-3">
                                                            <i class="fas {{ $producto->stock->cantidad > 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-2"></i>
                                                            {{ $producto->stock->cantidad > 0 ? $producto->stock->cantidad . ' unidades disponibles' : 'Producto agotado' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="button" class="btn btn-success">
                                                <i class="fas fa-shopping-cart me-2"></i>Solicitar información
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @empty
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-5x text-secondary mb-3"></i>
            <h3>No hay productos disponibles</h3>
            <p class="text-secondary">Próximamente tendremos novedades para ti</p>
        </div>
    @endforelse
</div>

<!-- Estilos adicionales -->
<style>
    .product-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 15px;
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }

    .btn-outline-success.active {
        background-color: #065f46;
        color: white;
        border-color: #065f46;
    }

    .btn-outline-success:hover {
        background-color: #047857;
        color: white;
    }

    .bg-success {
        background-color: #065f46 !important;
    }

    .text-success {
        color: #065f46 !important;
    }

    .input-group-text {
        border: none;
    }

    .card-header {
        border-bottom: 2px solid #065f46;
    }

    .modal-content {
        border-radius: 15px;
    }

    .modal-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
</style>

<!-- JavaScript para filtros -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('[data-filter]');
        const searchInput = document.getElementById('searchProduct');
        const products = document.querySelectorAll('.producto-item');

        let currentFilter = 'all';
        let currentSearch = '';

        function filterProducts() {
            products.forEach(product => {
                const tipo = product.dataset.tipo;
                const nombre = product.dataset.nombre;
                const matchesFilter = currentFilter === 'all' || tipo === currentFilter;
                const matchesSearch = nombre.includes(currentSearch.toLowerCase());

                if (matchesFilter && matchesSearch) {
                    product.style.display = '';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        // Filtros por tipo
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterProducts();
            });
        });

        // Búsqueda
        searchInput.addEventListener('keyup', function() {
            currentSearch = this.value;
            filterProducts();
        });
    });

    function verProducto(id) {
        // Función adicional si necesitas cargar datos dinámicamente
        console.log('Ver producto:', id);
    }
</script>
@endsection
