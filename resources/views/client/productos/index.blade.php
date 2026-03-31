@extends('layouts.cliente.app')

@section('title', 'Productos - Mi Empresa')
@section('page-title', 'Catálogo de Productos')

@section('content')
<div class="parent d-flex flex-column" style="height: 92vh">
    <!-- Mensajes de sesión -->
    <div class="row mb-4">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(isset($error))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>
    <!-- Barra de filtros (equivalente al div2) -->
    <div class="flex-shrink-0 bg-white py-3 border-bottom" style="margin-top: -50px">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Botones de filtro -->
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-success active" data-filter="all">
                        <i class="fas fa-th-large me-2"></i>Todos
                    </button>
                    <button class="btn btn-outline-success" data-filter="fisico">
                        <i class="fas fa-box me-2"></i>Físicos
                    </button>
                    <button class="btn btn-outline-success" data-filter="digital">
                        <i class="fas fa-cloud-download-alt me-2"></i>Digitales
                    </button>
                </div>

                <!-- Moneda y búsqueda -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if(isset($monedaActual))
                    <span class="badge bg-light text-dark rounded-pill py-2 px-3">
                        <i class="fas fa-money-bill-wave text-success me-1"></i>
                        <strong>{{ $monedaActual->simbolo ?? '$' }} {{ $monedaActual->codigo_iso ?? 'USD' }}</strong>
                    </span>
                    @endif

                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-success text-white border-0">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               class="form-control border-success"
                               id="searchProduct"
                               placeholder="Buscar productos...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor principal con 3 columnas -->
    <div class="row flex-grow-1 m-0 overflow-hidden">
        <!-- Columna izquierda: Categorías (3 columnas) -->
        <div class="col-12 col-md-3 h-100 p-3 overflow-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3 border-0">
                    <h5 class="mb-0 fs-6">
                        <i class="fas fa-list me-2"></i>Categorías
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    <!-- Opción TODAS las categorías -->
                    <button type="button"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center category-link active border-0"
                            data-categoria="all">
                        <span>
                            <i class="fas fa-th-large text-success me-2"></i>
                            Todas las categorías
                        </span>
                        <span class="badge bg-success rounded-pill">{{ $categorias->sum('productos_count') }}</span>
                    </button>

                    @forelse($categorias as $categoria)
                        <button type="button"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center category-link border-0"
                                data-categoria="{{ $categoria->id }}">
                            <span class="text-truncate">
                                <i class="fas fa-tag text-success me-2"></i>
                                {{ $categoria->nombre }}
                            </span>
                            <span class="badge bg-success rounded-pill">{{ $categoria->productos_count }}</span>
                        </button>
                    @empty
                        <div class="list-group-item text-center text-muted py-4 border-0">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0 small">No hay categorías</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna central: Productos (6 columnas) -->
        <div class="col-12 col-md-6 h-100 d-flex flex-column p-3 overflow-hidden">
            <!-- Área de productos con scroll -->
            <div class="flex-grow-1 overflow-auto products-scroll-area" style="max-height: calc(100vh - 160px); overflow-y: auto; padding-right: 15px;">
                <div id="productos-loader" class="text-center py-5 d-none">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando productos...</p>
                </div>

                <div id="productos-grid" class="row g-4">
                    <!-- Los productos se cargarán aquí -->
                </div>

                <div id="no-productos-message" class="text-center py-5 d-none">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron productos</p>
                </div>
            </div>

            <!-- Paginación -->
            <div id="pagination-container" class="mt-4 d-flex justify-content-center">
                <!-- La paginación se cargará aquí -->
            </div>
        </div>

        <!-- Columna derecha: Carrito (3 columnas) -->
        <div class="col-12 col-md-3 h-100 p-3 overflow-auto">
            @include('client.partials.carrito-sidebar')
        </div>
    </div>
</div>

<style>
/* Scrollbar personalizado */
.products-scroll-area::-webkit-scrollbar {
    width: 6px;
}
.products-scroll-area::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.products-scroll-area::-webkit-scrollbar-thumb {
    background: var(--bs-success);
    border-radius: 10px;
}
.products-scroll-area::-webkit-scrollbar-thumb:hover {
    background: #146c43;
}

/* Efectos hover */
.list-group-item-action:hover {
    background-color: rgba(40, 167, 69, 0.05) !important;
}

.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none !important;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

/* Sticky positioning */
.sticky-top {
    position: sticky;
    z-index: 1010;
}

/* Scroll para categorías */
.list-group-flush {
    scrollbar-width: thin;
}

/* ============================================ */
/* SOLO MODIFICO ESTA PARTE PARA MÓVIL */
/* ============================================ */
@media (max-width: 768px) {
    /* Cambiar el contenedor principal a altura automática en móvil */
    .parent {
        height: auto !important;
        min-height: 100vh;
    }

    /* Hacer que el row principal se apile verticalmente */
    .row.flex-grow-1 {
        flex-direction: column;
        overflow-y: visible !important;
        height: auto !important;
    }

    /* Cada columna en móvil: altura automática */
    .col-12 {
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Área de categorías en móvil */
    .col-md-3 .card {
        max-height: 300px;
    }

    /* Área de productos en móvil: quitar altura fija y permitir scroll normal */
    .products-scroll-area {
        max-height: none !important;
        overflow: visible !important;
        padding-right: 0 !important;
    }

    /* Grid de productos en móvil */
    #productos-grid {
        margin-bottom: 1rem;
    }

    /* Carrito en móvil */
    .col-md-3:last-child {
        margin-bottom: 1rem;
    }

    /* Ajuste de botones de filtro en móvil */
    .d-flex.gap-2 {
        justify-content: center;
        width: 100%;
    }

    /* Moneda y búsqueda en móvil */
    .d-flex.align-items-center.gap-3 {
        width: 100%;
        justify-content: center;
    }

    /* Input de búsqueda en móvil */
    .input-group {
        width: 100% !important;
    }

    /* Ajuste del margin-top negativo en móvil */
    .flex-shrink-0[style*="margin-top: -50px"] {
        margin-top: 0 !important;
    }
}

/* Pantallas muy pequeñas */
@media (max-width: 576px) {
    .product-card {
        margin-bottom: 1rem;
    }

    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }

    .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}
/* ============================================ */
/* FIN DE LAS MODIFICACIONES */
/* ============================================ */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Estado
    let currentPage = 1;
    let currentFilters = {
        tipo: 'all',
        categoria: 'all',
        search: ''
    };

    // Elementos
    const filterButtons = document.querySelectorAll('[data-filter]');
    const categoryLinks = document.querySelectorAll('.category-link');
    const searchInput = document.getElementById('searchProduct');
    const loader = document.getElementById('productos-loader');
    const grid = document.getElementById('productos-grid');
    const paginationContainer = document.getElementById('pagination-container');

    let searchTimeout;

    // Cargar productos iniciales
    cargarProductos();

    // Función para cargar productos desde API
    async function cargarProductos(page = 1) {
        try {
            // Mostrar loader
            loader.style.display = 'block';
            grid.innerHTML = '';

            // Construir URL con filtros
            const params = new URLSearchParams({
                page: page,
                per_page: 12
            });

            if (currentFilters.tipo !== 'all') {
                params.append('tipo', currentFilters.tipo);
            }

            if (currentFilters.categoria !== 'all') {
                params.append('categoria', currentFilters.categoria);
            }

            if (currentFilters.search) {
                params.append('search', currentFilters.search);
            }

            // Llamar a la API
            const response = await fetch(`/api/productos?${params}`);
            const data = await response.json();

            if (data.success) {
                renderizarProductos(data.data);
                renderizarPaginacion(data.meta);
            } else {
                mostrarError('Error al cargar productos');
            }

        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión');
        } finally {
            loader.style.display = 'none';
        }
    }

    // Renderizar productos
    function renderizarProductos(productos) {
        if (productos.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h5>No se encontraron productos</h5>
                        <p class="mb-0 small">Intenta con otros filtros</p>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        productos.forEach(p => {
            html += generarTarjetaProducto(p);
        });

        grid.innerHTML = html;

        // Reinicializar carruseles
        document.querySelectorAll('.carousel').forEach(carousel => {
            new bootstrap.Carousel(carousel, { interval: 3000 });
        });
    }

    // Generar HTML de tarjeta
    function generarTarjetaProducto(p) {
        return `
            <div class="col-xl-4 col-lg-6 col-md-6 producto-item">
                <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden product-card">
                    <!-- Badge digital -->
                    ${p.badges?.es_digital ? `
                        <span class="position-absolute top-0 start-0 m-2 badge bg-info z-3">
                            <i class="fas fa-cloud-download-alt me-1"></i>Digital
                        </span>
                    ` : ''}

                    <!-- Badge de descuento -->
                    ${p.badges?.tiene_descuento ? `
                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger z-3">
                            -${p.badges.porcentaje_descuento}%
                        </span>
                    ` : ''}

                    <!-- Carrusel -->
                    <div id="carousel-${p.id}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                        ${p.imagenes.cantidad > 1 ? `
                            <div class="carousel-indicators">
                                ${p.imagenes.todas.map((img, idx) => `
                                    <button type="button"
                                        data-bs-target="#carousel-${p.id}"
                                        data-bs-slide-to="${idx}"
                                        class="${idx === 0 ? 'active' : ''}">
                                    </button>
                                `).join('')}
                            </div>
                        ` : ''}

                        <div class="carousel-inner">
                            ${p.imagenes.todas.map((img, idx) => `
                                <div class="carousel-item ${idx === 0 ? 'active' : ''}">
                                    <img src="${img}"
                                        class="d-block w-100"
                                        alt="${p.nombre}"
                                        style="height: 200px; object-fit: cover;"
                                        onerror="this.src='${p.imagenes.principal}'">
                                </div>
                            `).join('')}
                        </div>

                        ${p.imagenes.cantidad > 1 ? `
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${p.id}" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-${p.id}" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        ` : ''}
                    </div>

                    <div class="card-body">
                        <h6 class="card-title text-truncate">${p.nombre}</h6>
                        ${p.sku ? `<small class="text-muted d-block mb-2"><i class="fas fa-barcode me-1"></i>SKU: ${p.sku}</small>` : ''}

                        <div class="mt-auto">
                            ${p.precios.tiene_descuento ? `
                                <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                    <span class="text-decoration-line-through text-muted small">${p.precios.original_formateado}</span>
                                    <span class="h5 mb-0 text-success fw-bold">${p.precios.con_descuento_formateado}</span>
                                    <span class="badge bg-light text-dark">${p.precios.moneda.codigo}</span>
                                </div>
                            ` : `
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="h5 mb-0 text-success fw-bold">${p.precios.original_formateado}</span>
                                    <span class="badge bg-light text-dark">${p.precios.moneda.codigo}</span>
                                </div>
                            `}

                            <div class="mt-1">
                                <small class="text-${p.stock.clase}">
                                    <i class="fas ${p.stock.icono} me-1"></i>${p.stock.texto}
                                </small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-outline-success flex-grow-1"
                                    onclick="agregarAlCarrito(${p.id}, this)"
                                    ${p.stock.actual === 0 && p.tipo === 'fisico' ? 'disabled' : ''}>
                                <i class="fas fa-cart-plus me-1"></i>Comprar
                            </button>
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="verProducto(${p.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Renderizar paginación
    function renderizarPaginacion(meta) {
        if (meta.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination justify-content-center">';

        // Anterior
        html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${meta.current_page - 1})">Anterior</a>
        </li>`;

        // Páginas
        for (let i = 1; i <= meta.last_page; i++) {
            if (i === 1 || i === meta.last_page || (i >= meta.current_page - 2 && i <= meta.current_page + 2)) {
                html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                </li>`;
            } else if (i === meta.current_page - 3 || i === meta.current_page + 3) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Siguiente
        html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${meta.current_page + 1})">Siguiente</a>
        </li>`;

        html += '</ul></nav>';
        paginationContainer.innerHTML = html;
    }

    // Cambiar página
    window.cambiarPagina = function(page) {
        currentPage = page;
        cargarProductos(page);
    };

    // Filtros
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentFilters.tipo = this.dataset.filter;
            currentPage = 1;
            cargarProductos(1);
        });
    });

    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            categoryLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            currentFilters.categoria = this.dataset.categoria;
            currentPage = 1;
            cargarProductos(1);
        });
    });

    // Búsqueda
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = this.value;
            currentPage = 1;
            cargarProductos(1);
        }, 300);
    });

    // Agregar al carrito
    window.agregarAlCarrito = async function(productoId, button) {
        const btn = button || event?.target;
        if (!btn) return;

        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';
        btn.disabled = true;

        try {
            const response = await fetch(`/carrito/agregar/${productoId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cantidad: 1 })
            });

            const data = await response.json();

            if (data.success) {
                if (typeof window.actualizarSidebarCarrito === 'function') {
                    window.actualizarSidebarCarrito();
                }
                btn.innerHTML = '✓';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 1000);
            } else {
                alert(data.error || 'Error al agregar el producto');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al agregar el producto al carrito');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };

    // Mostrar error
    function mostrarError(mensaje) {
        grid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5>Error</h5>
                    <p class="small mb-0">${mensaje}</p>
                </div>
            </div>
        `;
    }

    // Función global para ver producto
    window.verProducto = function(id) {
        window.location.href = `/productos/${id}`;
    };
});
</script>
@endsection
