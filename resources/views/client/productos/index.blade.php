@extends('layouts.cliente.app')

@section('title', 'Productos - Mi Empresa')
@section('page-title', 'Catálogo de Productos')

@section('content')
<div class="container-fluid py-4">
    <!-- Filtros y búsqueda (ocupan todo el ancho) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
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

                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-success text-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchProduct" placeholder="Buscar productos...">
                </div>
            </div>
        </div>
    </div>

    <!-- Grid principal: 5 columnas -->
    <div class="row g-4">
        <!-- Columna izquierda: Categorías (ocupa 1 columna) -->
        <div class="col-lg-2">
            <div class="card shadow-sm border-0 sticky-top" style="top: 80px; z-index: 99;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Categorías
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active d-flex justify-content-between align-items-center" data-categoria="all">
                            Todas las categorías
                            <span class="badge bg-success rounded-pill">{{ $productos->count() }}</span>
                        </a>
                        @forelse($categorias as $categoria)
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                               data-categoria="{{ $categoria->id }}">
                                <span>
                                    <i class="fas fa-folder me-2 text-success"></i>
                                    {{ $categoria->nombre }}
                                </span>
                                <span class="badge bg-success rounded-pill">
                                    {{ $categoria->productos->where('is_active', true)->count() }}
                                </span>
                            </a>
                        @empty
                            <div class="list-group-item text-secondary">
                                No hay categorías
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna central: Productos (ocupa 3 columnas) -->
        <div class="col-lg-7">
            @if($productos->count() > 0)
                <div class="row g-4" id="productosGrid">
                    @foreach($productos as $producto)
                        <div class="col-xl-4 col-lg-6 col-md-6 producto-item"
                             data-tipo="{{ $producto->tipo_producto }}"
                             data-nombre="{{ strtolower($producto->nombre) }}"
                             data-categoria="{{ $producto->categoria_id }}">
                            <div class="card h-100 shadow-sm product-card border-0">
                                <!-- Carrusel de imágenes -->
                                <div id="carousel{{ $producto->id }}" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @php
                                            $images = [];
                                            for($i = 1; $i <= 5; $i++) {
                                                $imgField = "imagen_url_$i";
                                                if($producto->$imgField) {
                                                    $images[] = $producto->$imgField;
                                                }
                                            }
                                        @endphp

                                        @if(count($images) > 0)
                                            @foreach($images as $index => $image)
                                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                    <img src="{{ $image }}"
                                                         class="d-block w-100"
                                                         alt="{{ $producto->nombre }} - Imagen {{ $index + 1 }}"
                                                         style="height: 200px; object-fit: cover;">
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="carousel-item active">
                                                <div class="bg-light d-flex align-items-center justify-content-center"
                                                     style="height: 200px;">
                                                    <i class="fas fa-image fa-4x text-secondary"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if(count($images) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $producto->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Anterior</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $producto->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Siguiente</span>
                                        </button>
                                    @endif

                                    <!-- Badge de tipo de producto -->
                                    <span class="position-absolute top-0 end-0 m-2 badge z-index-1
                                        {{ $producto->tipo_producto == 'digital' ? 'bg-info' : 'bg-primary' }}">
                                        <i class="fas {{ $producto->tipo_producto == 'digital' ? 'fa-cloud' : 'fa-box' }} me-1"></i>
                                        {{ ucfirst($producto->tipo_producto) }}
                                    </span>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title fw-bold">{{ $producto->nombre }}</h5>

                                    @if($producto->descripcion)
                                        <p class="card-text text-secondary small">
                                            {{ Str::limit($producto->descripcion, 60) }}
                                        </p>
                                    @endif

                                    <!-- Categoría -->
                                    <div class="mb-2">
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $producto->categoria->nombre }}
                                        </span>
                                    </div>

                                    <!-- SKU -->
                                    @if($producto->sku)
                                        <p class="small text-secondary mb-2">
                                            <i class="fas fa-barcode me-1"></i>
                                            SKU: {{ $producto->sku }}
                                        </p>
                                    @endif

                                    <!-- Stock -->
                                    @if($producto->esFisico() && $producto->stock)
                                        @if($producto->stock->cantidad > 0)
                                            <span class="badge bg-success text-white mb-2">
                                                <i class="fas fa-check-circle me-1"></i>
                                                {{ $producto->stock->cantidad }} disponibles
                                            </span>
                                        @else
                                            <span class="badge bg-danger text-white mb-2">
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
                                                Agregar
                                            </button>
                                        @endif

                                        <a href="{{ route('producto.detalle', $producto->id) }}" class="btn btn-outline-success">
                                            <i class="fas fa-eye me-2"></i>
                                            Detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-5x text-secondary mb-3"></i>
                    <h3>No hay productos disponibles</h3>
                    <p class="text-secondary">Próximamente tendremos novedades para ti</p>
                </div>
            @endif
        </div>

        <!-- Columna derecha: Carrito/Información (ocupa 2 columnas) -->
        <div class="col-lg-3">
            <div class="sticky-top" style="top: 80px; z-index: 99;">
                <!-- Carrito de compras -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>Mi Carrito
                            <span class="badge bg-light text-success float-end" id="cartCount">0</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="cartItems">
                            <div class="text-center text-secondary py-4">
                                <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">Tu carrito está vacío</p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span class="text-success" id="cartTotal">$0.00</span>
                        </div>
                        <button class="btn btn-success w-100 mt-3" disabled>
                            Proceder al pago
                        </button>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="fas fa-truck text-success me-2"></i>
                                <strong>Envíos:</strong> A todo el país
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-credit-card text-success me-2"></i>
                                <strong>Pagos:</strong> Transferencia, tarjetas
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-clock text-success me-2"></i>
                                <strong>Horario:</strong> Lun - Vie 9:00 a 18:00
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-headset text-success me-2"></i>
                                <strong>Soporte:</strong> 24/7 vía chat
                            </li>
                        </ul>

                        <hr>

                        <div class="text-center">
                            <p class="fw-bold mb-2">¿Necesitas ayuda?</p>
                            <a href="#" class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-comments me-2"></i>Chat en vivo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

    .list-group-item.active {
        background-color: #065f46;
        border-color: #065f46;
    }

    .list-group-item-action:hover {
        background-color: #f0fdf4;
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 15%;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .product-card:hover .carousel-control-prev,
    .product-card:hover .carousel-control-next {
        opacity: 0.5;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1 !important;
    }

    .z-index-1 {
        z-index: 10;
    }

    /* Estilo para el grid */
    .sticky-top {
        z-index: 99;
    }

    @media (max-width: 992px) {
        .sticky-top {
            position: relative !important;
            top: 0 !important;
        }
    }
</style>

<!-- JavaScript para filtros y carrito -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('[data-filter]');
        const categoriaLinks = document.querySelectorAll('[data-categoria]');
        const searchInput = document.getElementById('searchProduct');
        const products = document.querySelectorAll('.producto-item');

        let currentFilter = 'all';
        let currentCategoria = 'all';
        let currentSearch = '';

        function filterProducts() {
            products.forEach(product => {
                const tipo = product.dataset.tipo;
                const categoria = product.dataset.categoria;
                const nombre = product.dataset.nombre;

                const matchesFilter = currentFilter === 'all' || tipo === currentFilter;
                const matchesCategoria = currentCategoria === 'all' || categoria == currentCategoria;
                const matchesSearch = nombre.includes(currentSearch.toLowerCase());

                if (matchesFilter && matchesCategoria && matchesSearch) {
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

        // Filtros por categoría
        categoriaLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                categoriaLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                currentCategoria = this.dataset.categoria;
                filterProducts();
            });
        });

        // Búsqueda
        searchInput.addEventListener('keyup', function() {
            currentSearch = this.value;
            filterProducts();
        });
    });

    // Variables del carrito
    let cart = [];

    function agregarAlCarrito(id) {
        // Aquí iría la lógica para agregar al carrito
        console.log('Agregar al carrito producto ID:', id);

        // Simulación de carrito
        const cartCount = document.getElementById('cartCount');
        const currentCount = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = currentCount + 1;

        // Mostrar mensaje
        alert('Producto agregado al carrito');
    }
</script>
@endsection
