@extends('layouts.cliente.app')

@section('title', $producto->nombre . ' - Mi Empresa')
@section('page-title', 'Detalle del Producto')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('producto.index') }}" class="text-success">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $producto->nombre }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Columna izquierda: Producto -->
        <div class="col-lg-9">
            <div class="row g-4">
                <!-- Galería de imágenes -->
                <div class="col-md-6">
                    <?php
                        $images = \App\Helpers\ImagenesHelper::getImagenesProducto($producto);
                        $defaultImage = \App\Helpers\ImagenesHelper::getDefaultImage();
                    ?>

                    <!-- Imagen principal -->
                    <div class="main-image-container mb-3">
                        <img id="mainProductImage"
                             src="{{ count($images) > 0 ? $images[0] : $defaultImage }}"
                             class="img-fluid rounded-3 shadow w-100"
                             alt="{{ $producto->nombre }}"
                             style="height: 400px; object-fit: contain; background-color: #f8f9fa;"
                             onerror="this.onerror=null; this.src='{{ $defaultImage }}';">
                    </div>

                    <!-- Miniaturas -->
                    @if(count($images) > 1)
                        <div class="thumbnail-gallery d-flex gap-2 flex-wrap">
                            @foreach($images as $index => $image)
                                <div class="thumbnail-item {{ $index == 0 ? 'active' : '' }}"
                                     onclick="changeMainImage('{{ $image }}', this)"
                                     style="cursor: pointer; border: 2px solid {{ $index == 0 ? '#065f46' : 'transparent' }}; border-radius: 8px; overflow: hidden; transition: all 0.3s;">
                                    <img src="{{ $image }}"
                                         alt="Miniatura {{ $index + 1 }}"
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ $defaultImage }}';">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Información del producto -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <!-- Etiquetas de estado -->
                            <div class="d-flex gap-2 mb-3 flex-wrap">
                                @if($producto->created_at && $producto->created_at->diffInDays(now()) <= 30)
                                    <span class="badge bg-success">Nuevo</span>
                                @endif
                                @if($producto->esDigital())
                                    <span class="badge bg-info">Producto Digital</span>
                                @endif
                                @if($precioInfo['tiene_descuento'])
                                    <span class="badge bg-danger">-{{ $precioInfo['porcentaje_descuento'] }}% OFF</span>
                                @endif
                            </div>

                            <h1 class="fw-bold mb-3">{{ $producto->nombre }}</h1>

                            @if($producto->sku)
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-barcode me-1"></i> SKU: {{ $producto->sku }}
                                </div>
                            @endif

                            <!-- Precios con descuento -->
                            <div class="mb-3">
                                @if($precioInfo['tiene_descuento'])
                                    <div class="d-flex align-items-baseline gap-3 flex-wrap">
                                        <span class="text-secondary text-decoration-line-through fs-5">
                                            {{ $precioInfo['precio_original_formateado'] }}
                                        </span>
                                        <span class="fw-bold text-success fs-1">
                                            {{ $precioInfo['precio_con_descuento_formateado'] }}
                                        </span>
                                        <span class="badge bg-success fs-6 px-3 py-2">
                                            Ahorra {{ $precioInfo['ahorro_formateado'] }}
                                        </span>
                                    </div>
                                @else
                                    <span class="fw-bold text-success fs-1">
                                        {{ $precioInfo['precio_original_formateado'] }}
                                    </span>
                                @endif
                            </div>

                            <!-- Descripción -->
                            @if($producto->descripcion)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-2">Descripción</h6>
                                    <p class="text-secondary">{{ $producto->descripcion }}</p>
                                </div>
                            @endif

                            <!-- Características del producto -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Características</h6>
                                <ul class="list-unstyled">
                                    @if($producto->esDigital())
                                        <li class="mb-2">
                                            <i class="fas fa-cloud-download-alt text-success me-2"></i>
                                            <strong>Tipo:</strong> Producto digital
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-tachometer-alt text-success me-2"></i>
                                            <strong>Entrega:</strong> Descarga inmediata después de la compra
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-infinity text-success me-2"></i>
                                            <strong>Stock:</strong> Ilimitado
                                        </li>
                                    @else
                                        <li class="mb-2">
                                            <i class="fas fa-box text-success me-2"></i>
                                            <strong>Tipo:</strong> Producto físico
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-truck text-success me-2"></i>
                                            <strong>Envío:</strong> A todo el país
                                        </li>
                                        @if($producto->stock_actual !== null)
                                            <li class="mb-2">
                                                <i class="fas fa-cubes text-success me-2"></i>
                                                <strong>Stock disponible:</strong> {{ $producto->stock_actual }} unidades
                                            </li>
                                        @endif
                                    @endif
                                </ul>
                            </div>

                            <!-- Stock para productos físicos -->
                            @if($producto->esFisico())
                                <div class="mb-4">
                                    @if($producto->stock_actual > 10)
                                        <span class="badge bg-success py-2 px-3">
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ $producto->stock_actual }} unidades disponibles
                                        </span>
                                    @elseif($producto->stock_actual > 0)
                                        <span class="badge bg-warning py-2 px-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            ¡Últimas {{ $producto->stock_actual }} unidades!
                                        </span>
                                    @else
                                        <span class="badge bg-danger py-2 px-3">
                                            <i class="fas fa-times-circle me-1"></i>
                                            Producto agotado
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Botones de acción -->
                            <div class="d-grid gap-3">
                                @if($producto->esFisico() && $producto->stock_actual == 0)
                                    <button class="btn btn-secondary btn-lg" disabled>
                                        <i class="fas fa-times-circle me-2"></i>
                                        Producto no disponible
                                    </button>
                                @else
                                    <button class="btn btn-success btn-lg" onclick="agregarAlCarrito({{ $producto->id }})">
                                        <i class="fas fa-cart-plus me-2"></i>
                                        Agregar al carrito
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Carrito Sidebar -->
        <div class="col-lg-3">
            @include('client.partials.carrito-sidebar')
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
    .thumbnail-gallery {
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .thumbnail-item {
        transition: all 0.3s ease;
    }

    .thumbnail-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .thumbnail-item.active {
        border-color: #065f46 !important;
    }

    .main-image-container {
        background-color: #f8f9fa;
        border-radius: 12px;
        overflow: hidden;
    }

    /* Scrollbar personalizado para miniaturas */
    .thumbnail-gallery::-webkit-scrollbar {
        height: 6px;
    }

    .thumbnail-gallery::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .thumbnail-gallery::-webkit-scrollbar-thumb {
        background: #065f46;
        border-radius: 10px;
    }

    .thumbnail-gallery::-webkit-scrollbar-thumb:hover {
        background: #047857;
    }

    @media (max-width: 768px) {
        .main-image-container img {
            height: 300px !important;
        }
    }
</style>

<script>
// Cambiar imagen principal
function changeMainImage(imageUrl, element) {
    const mainImage = document.getElementById('mainProductImage');
    if (mainImage) {
        mainImage.src = imageUrl;
    }

    // Actualizar estado activo de miniaturas
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.style.borderColor = 'transparent';
        item.classList.remove('active');
    });

    if (element) {
        element.style.borderColor = '#065f46';
        element.classList.add('active');
    }
}

// Agregar al carrito
function agregarAlCarrito(productoId) {
    const button = event?.currentTarget;
    if (!button) return;

    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Agregando...';
    button.disabled = true;

    fetch(`/carrito/agregar/${productoId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
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

            // Mostrar notificación de éxito
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion('Producto agregado al carrito', 'success');
            } else {
                // Feedback visual temporal
                button.innerHTML = '<i class="fas fa-check me-2"></i>¡Agregado!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
                return;
            }
        } else {
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion(data.error || 'No se pudo agregar el producto', 'danger');
            } else {
                alert(data.error || 'No se pudo agregar el producto');
            }
        }
        button.innerHTML = originalText;
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Error al agregar el producto', 'danger');
        } else {
            alert('Error al agregar el producto');
        }
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>
@endsection
