@extends('layouts.cliente.app')

@section('title', 'Productos - Mi Empresa')
@section('page-title', 'Catálogo de Productos')

@section('content')
<div class="container-fluid py-4">
    <!-- Filtros y búsqueda con indicador de moneda (se mantiene igual) -->
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

    <!-- Mensajes de sesión (se mantiene igual) -->
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

    <!-- Grid principal con columnas sticky -->
    <div class="row g-4">
        <!-- Columna izquierda: Categorías (STICKY) -->
        <div class="col-lg-2">
            <div class="sticky-top" style="top: 80px;">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Categorías
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: calc(100vh - 120px); overflow-y: auto;">
                            @if(isset($categorias) && $categorias->count() > 0)
                                @foreach($categorias as $categoria)
                                    <a href="#categoria-{{ $categoria->id }}"
                                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-tag me-2 text-success"></i>
                                            {{ $categoria->nombre }}
                                        </span>
                                        <span class="badge bg-success rounded-pill">{{ $categoria->productos->count() }}</span>
                                    </a>
                                @endforeach
                            @else
                                <div class="list-group-item text-center text-muted py-4">
                                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                                    <p class="mb-0">No hay categorías</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Columna central: Productos -->
        <div class="col-lg-7">
            <!-- Grid de productos -->
            <div id="productos-grid" class="row g-4">
                @forelse($productos as $producto)
                    <div class="col-xl-4 col-lg-6 col-md-6 producto-item"
                        data-categoria="{{ $producto->categoria_id }}"
                        data-tipo="{{ $producto->tipo_producto }}"
                        data-nombre="{{ strtolower($producto->nombre) }}">

                        <!-- Tarjeta de producto -->
                        <div class="card h-100 border-0 shadow-sm {{ $producto->card_class }} position-relative">

                            <!-- Badge para producto digital -->
                            @if($producto->es_digital)
                                <span class="{{ $producto->badge_digital_class }}">
                                    <i class="fas fa-cloud-download-alt me-1"></i>Digital
                                </span>
                            @endif

                            <!-- Badge de descuento -->
                            @if($producto->tiene_descuento)
                                <span class="{{ $producto->badge_descuento_class }}">
                                    -{{ $producto->porcentaje_descuento }}%
                                </span>
                            @endif

                            <!-- Carrusel de imágenes -->
                            @if($producto->tiene_imagenes)
                                <div id="carousel-{{ $producto->id }}" class="carousel slide" data-bs-ride="false">
                                    <div class="carousel-inner">
                                        @foreach($producto->imagenes as $index => $imagen)
                                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                <img src="{{ $imagen }}"
                                                    class="d-block w-100"
                                                    alt="{{ $producto->nombre }}"
                                                    style="height: 180px; object-fit: cover;"
                                                    onerror="this.src='{{ $producto->default_image }}'">
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($producto->cantidad_imagenes > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{ $producto->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Anterior</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{ $producto->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Siguiente</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                    <img src="{{ $producto->default_image }}"
                                        alt="Sin imagen"
                                        style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                            @endif

                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title mb-1">{{ $producto->nombre }}</h6>

                                @if($producto->descripcion_corta)
                                    <p class="card-text small text-muted mb-2">
                                        {{ $producto->descripcion_corta }}
                                    </p>
                                @endif

                                <!-- SKU para productos físicos -->
                                @if($producto->es_fisico && $producto->sku)
                                    <small class="text-muted mb-2">
                                        <i class="fas fa-barcode me-1"></i>SKU: {{ $producto->sku }}
                                    </small>
                                @endif

                                <!-- Precios (ya formateados desde el controlador) -->
                                <div class="mt-auto">
                                    @if($producto->tiene_descuento)
                                        <div class="d-flex align-items-baseline">
                                            <span class="text-decoration-line-through text-muted small me-2">
                                                {{ $producto->precio_original_formateado }}
                                            </span>
                                            <span class="h5 mb-0 text-success fw-bold">
                                                {{ $producto->precio_con_descuento_formateado }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="h5 mb-0 text-success fw-bold">
                                            {{ $producto->precio_original_formateado }}
                                        </span>
                                    @endif

                                    <!-- Stock (ya procesado desde el controlador) -->
                                    <div class="mt-1">
                                        <small class="text-{{ $producto->clase_stock }}">
                                            <i class="fas {{ $producto->icono_stock }} me-1"></i>
                                            {{ $producto->texto_stock }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-sm btn-outline-success flex-grow-1 {{ $producto->btn_comprar_class }}"
                                            data-producto-id="{{ $producto->id }}"
                                            {{ !$producto->tiene_stock ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus me-1"></i>Comprar
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary {{ $producto->btn_ver_class }}"
                                            onclick="verProducto({{ $producto->id }})"
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-store fa-3x mb-3"></i>
                            <h4>No hay productos disponibles</h4>
                            <p class="mb-0">Pronto tendremos productos para ti.</p>
                        </div>
                    </div>
                @endforelse

                <!-- Paginación -->
                @if($productos->hasPages())
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-center">
                            {{ $productos->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna derecha: Carrito/Información (STICKY) -->
        <div class="col-lg-3">
            <div class="sticky-top" style="top: 80px;"> <!-- Mismo offset que la izquierda -->
                @include('client.partials.carrito-sidebar')

                <!-- Espacio adicional para el footer si es necesario -->
                <div class="mt-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="fas fa-info-circle me-2 text-success"></i>Información</h6>
                            <hr class="my-2">
                            <p class="small text-secondary mb-0">
                                <i class="fas fa-truck me-2"></i>Envíos a todo el país<br>
                                <i class="fas fa-shield-alt me-2 mt-2"></i>Compra protegida
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales para sticky y paginación -->
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

    /* Estilos para sticky sidebars */
    .sticky-sidebar {
        position: sticky;
        top: 20px;
        z-index: 100;
    }

    /* Estilos personalizados para la paginación */
    .pagination {
        gap: 5px;
    }

    .pagination .page-link {
        color: #065f46;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 8px 15px;
        transition: all 0.3s;
    }

    .pagination .page-link:hover {
        background-color: #065f46;
        color: white;
        border-color: #065f46;
    }

    .pagination .active .page-link {
        background-color: #065f46;
        border-color: #065f46;
        color: white;
    }

    .pagination .disabled .page-link {
        color: #9ca3af;
        pointer-events: none;
    }

    /* Mejoras para móviles */
    @media (max-width: 992px) {
        .sticky-sidebar {
            position: static;
            margin-bottom: 20px;
        }
    }
</style>

<!-- JavaScript actualizado -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const filterButtons = document.querySelectorAll('[data-filter]');
        const categoriaLinks = document.querySelectorAll('.categoria-link');
        const searchInput = document.getElementById('searchProduct');
        const products = document.querySelectorAll('.producto-item');
        const addToCartButtons = document.querySelectorAll('.btn-agregar-carrito');
        const totalProductosSpan = document.getElementById('totalProductosCount');

        let currentFilter = 'all';
        let currentCategoria = 'all';
        let currentSearch = '';

        // Función para actualizar contadores de categorías
        function actualizarContadores() {
            const categoriaCounts = document.querySelectorAll('.categoria-count');
            let totalVisible = 0;

            categoriaCounts.forEach(countSpan => {
                const categoriaId = countSpan.dataset.categoria;
                let count = 0;

                products.forEach(product => {
                    if (product.dataset.categoria === categoriaId &&
                        product.style.display !== 'none') {
                        count++;
                    }
                });

                countSpan.textContent = count;
                totalVisible += count;
            });

            // Actualizar contador total
            if (totalProductosSpan) {
                totalProductosSpan.textContent = totalVisible;
            }

            // Actualizar clases activas de categorías
            categoriaLinks.forEach(link => {
                const linkCategoria = link.dataset.categoria;
                let hasVisible = false;

                if (linkCategoria === 'all') {
                    hasVisible = totalVisible > 0;
                } else {
                    products.forEach(product => {
                        if (product.dataset.categoria === linkCategoria &&
                            product.style.display !== 'none') {
                            hasVisible = true;
                        }
                    });
                }

                if (!hasVisible && linkCategoria !== 'all') {
                    link.style.display = 'none';
                } else {
                    link.style.display = '';
                }
            });
        }

        // Función para filtrar productos (actualizada)
        function filterProducts() {
            let visibleCount = 0;

            products.forEach(product => {
                const tipo = product.dataset.tipo;
                const categoria = product.dataset.categoria;
                const nombre = product.dataset.nombre;

                const matchesFilter = currentFilter === 'all' || tipo === currentFilter;
                const matchesCategoria = currentCategoria === 'all' || categoria === currentCategoria;
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

            // Actualizar contadores
            actualizarContadores();
        }

        // Event listeners para filtros
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

        // Búsqueda con debounce
        let searchTimeout;
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = this.value;
                filterProducts();
            }, 300);
        });

        // Agregar al carrito (se mantiene igual)
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
