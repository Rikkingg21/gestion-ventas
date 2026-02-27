@extends('layouts.cliente.app')

@section('title', 'Productos - Mi Empresa')
@section('page-title', 'Catálogo de Productos')

@section('content')
<div class="container-fluid py-4">
    <!-- Filtros y búsqueda con indicador de moneda -->
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

                <div class="d-flex align-items-center gap-3">
                    <!-- Indicador de moneda actual -->
                    @if(isset($monedaActual))
                    <span class="badge bg-success bg-opacity-10 text-white p-2">
                        <i class="fas fa-money-bill-wave me-1"></i>
                        Moneda:
                        <strong>{{ $monedaActual->simbolo }} {{ $monedaActual->codigo_iso }}</strong>

                        @if(isset($userGeoInfo['country']))
                            <span class="ms-1">
                                <span class="flag-icon flag-icon-{{ $userGeoInfo['flag'] }}"></span>
                                {{ $userGeoInfo['country'] }}
                            </span>
                        @endif
                    </span>
                    @endif

                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-success text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchProduct" placeholder="Buscar productos...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de sesión -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Grid principal -->
    <div class="row g-4">
        <!-- Columna izquierda: Categorías -->
        <div class="col-lg-2">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Categorías
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active d-flex justify-content-between align-items-center categoria-link" data-categoria="all">
                            Todas las categorías
                            <span class="badge bg-success rounded-pill">{{ $productosProcesados->count() }}</span>
                        </a>
                        @forelse($categorias as $categoria)
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center categoria-link"
                               data-categoria="{{ $categoria->id }}">
                                <span>
                                    <i class="fas fa-folder me-2 text-success"></i>
                                    {{ $categoria->nombre }}
                                </span>
                                <span class="badge bg-success rounded-pill">
                                    {{ $productosProcesados->where('categoria.id', $categoria->id)->count() }}
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

        <!-- Columna central: Productos -->
        <div class="col-lg-7">
            @if($productosProcesados->count() > 0)
                <div class="row g-4" id="productosGrid">
                    @foreach($productosProcesados as $producto)
                        <div class="col-xl-4 col-lg-6 col-md-6 producto-item"
                             data-tipo="{{ $producto->tipo_producto }}"
                             data-nombre="{{ strtolower($producto->nombre) }}"
                             data-categoria="{{ $producto->categoria->id }}">
                            <div class="card h-100 shadow-sm product-card border-0">
                                <!-- Carrusel de imágenes -->
                                <div id="carousel{{ $producto->id }}" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @if(count($producto->imagenes) > 0)
                                            @foreach($producto->imagenes as $index => $image)
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

                                    @if(count($producto->imagenes) > 1)
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

                                    <!-- Badge de descuento -->
                                    @if($producto->tiene_descuento)
                                        <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark z-index-1">
                                            <i class="fas fa-tag me-1"></i>
                                            -{{ $producto->porcentaje_descuento }}%
                                        </span>
                                    @endif
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title fw-bold">{{ $producto->nombre }}</h5>

                                    @if($producto->descripcion_corta)
                                        <p class="card-text text-secondary small">
                                            {{ $producto->descripcion_corta }}
                                        </p>
                                    @endif

                                    <!-- Categoría -->
                                    <div class="mb-2">
                                        <span class="badge bg-success bg-opacity-10 text-white">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $producto->categoria->nombre }}
                                        </span>
                                    </div>

                                    <!-- SKU (solo físicos) -->
                                    @if($producto->es_fisico && $producto->sku)
                                        <p class="small text-secondary mb-2">
                                            <i class="fas fa-barcode me-1"></i>
                                            SKU: {{ $producto->sku }}
                                        </p>
                                    @endif

                                    <!-- URL Recurso (solo digitales) -->
                                    @if($producto->es_digital && $producto->url_recurso)
                                        <p class="small text-secondary mb-2" title="{{ $producto->url_recurso }}">
                                            <i class="fas fa-link me-1"></i>
                                            Recurso digital disponible
                                        </p>
                                    @endif

                                    <!-- Stock -->
                                    <span class="badge bg-{{ $producto->clase_stock }} text-white mb-2">
                                        <i class="fas {{ $producto->icono_stock }} me-1"></i>
                                        {{ $producto->texto_stock }}
                                    </span>

                                    <!-- Precios -->
                                    <div class="mt-3">
                                        @if($producto->tiene_descuento)
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-secondary">{{ $producto->moneda_actual['codigo'] }}:</span>
                                                <div class="text-end">
                                                    <span class="text-decoration-line-through text-secondary me-2 small">
                                                        {{ $producto->precio_original_formateado }}
                                                    </span>
                                                    <span class="fw-bold text-success">
                                                        {{ $producto->precio_con_descuento_formateado }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="small text-warning mt-1">
                                                <i class="fas fa-tag me-1"></i>
                                                Ahorras {{ $producto->ahorro_formateado }} ({{ $producto->porcentaje_descuento }}%)
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-secondary">{{ $producto->moneda_actual['codigo'] }}:</span>
                                                <span class="fw-bold text-success">
                                                    {{ $producto->precio_original_formateado }}
                                                </span>
                                            </div>
                                        @endif

                                        <!-- Mostrar otras monedas como referencia -->
                                        @if($producto->otras_monedas->count() > 0)
                                            <div class="mt-2 small text-secondary">
                                                <i class="fas fa-info-circle me-1"></i>
                                                También en:
                                                @foreach($producto->otras_monedas as $otraMoneda)
                                                    <span class="badge bg-light text-dark me-1" title="{{ $otraMoneda['codigo'] }}">
                                                        {{ $otraMoneda['simbolo'] }}{{ $otraMoneda['precio'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-0 pb-3">
                                    <div class="d-grid gap-2">
                                        @if(!$producto->tiene_stock)
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-times-circle me-2"></i>
                                                No disponible
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-success btn-agregar-carrito" data-producto-id="{{ $producto->id }}">
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

        <!-- Columna derecha: Carrito/Información (sin cambios) -->
        <div class="col-lg-3">
            @include('client.partials.carrito-sidebar')
            <!-- ... resto del sidebar ... -->
        </div>
    </div>
</div>

<!-- Estilos (igual que antes) -->
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

    /* Animación para mensajes */
    .alert {
        animation: slideDown 0.5s ease-out;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<!-- JavaScript (simplificado) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const filterButtons = document.querySelectorAll('[data-filter]');
        const categoriaLinks = document.querySelectorAll('.categoria-link');
        const searchInput = document.getElementById('searchProduct');
        const products = document.querySelectorAll('.producto-item');
        const addToCartButtons = document.querySelectorAll('.btn-agregar-carrito');

        let currentFilter = 'all';
        let currentCategoria = 'all';
        let currentSearch = '';

        // Función para filtrar productos (misma lógica)
        function filterProducts() {
            let visibleCount = 0;

            products.forEach(product => {
                const tipo = product.dataset.tipo;
                const categoria = product.dataset.categoria;
                const nombre = product.dataset.nombre;

                const matchesFilter = currentFilter === 'all' || tipo === currentFilter;
                const matchesCategoria = currentCategoria === 'all' || categoria == currentCategoria;
                const matchesSearch = nombre.includes(currentSearch.toLowerCase());

                if (matchesFilter && matchesCategoria && matchesSearch) {
                    product.style.display = '';
                    visibleCount++;
                } else {
                    product.style.display = 'none';
                }
            });

            // Mostrar/ocultar mensaje de "No hay resultados"
            const grid = document.getElementById('productosGrid');
            let noResultsMessage = document.getElementById('noResultsMessage');

            if (visibleCount === 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('div');
                    message.id = 'noResultsMessage';
                    message.className = 'col-12 text-center py-5';
                    message.innerHTML = `
                        <i class="fas fa-search fa-4x text-secondary mb-3"></i>
                        <h4>No se encontraron productos</h4>
                        <p class="text-secondary">Intenta con otros filtros o búsqueda</p>
                    `;
                    grid.appendChild(message);
                }
            } else {
                if (noResultsMessage) {
                    noResultsMessage.remove();
                }
            }
        }

        // Event listeners (igual que antes)
        filterButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterProducts();
            });
        });

        categoriaLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                categoriaLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                currentCategoria = this.dataset.categoria;
                filterProducts();
            });
        });

        let searchTimeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = this.value;
                filterProducts();
            }, 300);
        });

        // Agregar al carrito (igual que antes)
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productoId = this.dataset.productoId;

                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Agregando...';
                this.disabled = true;

                fetch(`/carrito/agregar/${productoId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ cantidad: 1 })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof window.actualizarSidebarCarrito === 'function') {
                            window.actualizarSidebarCarrito();
                        }
                    } else {
                        if (typeof window.mostrarNotificacion === 'function') {
                            window.mostrarNotificacion(data.error || 'No se pudo agregar el producto', 'danger');
                        } else {
                            alert(data.error || 'No se pudo agregar el producto');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof window.mostrarNotificacion === 'function') {
                        window.mostrarNotificacion('Error al agregar el producto al carrito', 'danger');
                    } else {
                        alert('Error al agregar el producto al carrito');
                    }
                })
                .finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });
        });

        // Auto-cerrar alertas
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endsection
