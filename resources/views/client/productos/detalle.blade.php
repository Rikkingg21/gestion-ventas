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
                    <!-- Imagen principal -->
                    <div class="main-image-container mb-3">
                        <img id="mainProductImage"
                             src="{{ count($images) > 0 ? $images[0] : asset('images/placeholder.jpg') }}"
                             class="img-fluid rounded-3 shadow w-100"
                             alt="{{ $producto->nombre }}"
                             style="height: 400px; object-fit: contain; background-color: #f8f9fa;"
                             onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}';">
                    </div>

                    <!-- Miniaturas -->
                    @if(count($images) > 0)
                        <div class="thumbnail-gallery d-flex gap-2 flex-wrap">
                            @foreach($images as $index => $image)
                                <div class="thumbnail-item {{ $index == 0 ? 'active' : '' }}"
                                     onclick="changeMainImage('{{ $image }}', this)"
                                     style="cursor: pointer; border: 2px solid {{ $index == 0 ? '#065f46' : 'transparent' }}; border-radius: 8px; overflow: hidden; transition: all 0.3s;">
                                    <img src="{{ $image }}"
                                         alt="Miniatura {{ $index + 1 }}"
                                         style="width: 80px; height: 80px; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='{{ asset('images/placeholder-thumb.jpg') }}';">
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
                                @if($productData['is_new'])
                                    <span class="badge bg-success">Nuevo</span>
                                @endif
                                <span class="badge bg-warning text-dark">+5 vendidos</span>
                            </div>

                            <h1 class="fw-bold mb-3">{{ $producto->nombre }}</h1>

                            <!-- Rating -->
                            @if(isset($productData['rating']))
                                <div class="d-flex align-items-center mb-3">
                                    <div class="text-warning me-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($productData['rating']))
                                                <i class="fas fa-star"></i>
                                            @elseif($i - $productData['rating'] <= 0.5)
                                                <i class="fas fa-star-half-alt"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-secondary">({{ $productData['total_reviews'] }} reseñas)</span>
                                </div>
                            @endif

                            <!-- Precios con descuento -->
                            @if($productData['precio_con_descuento']['tiene_descuento'])
                                <div class="mb-3">
                                    <span class="text-secondary text-decoration-line-through fs-5 me-2">
                                        S/ {{ number_format($productData['precio_con_descuento']['precio_original_local'], 2) }}
                                    </span>
                                    <span class="fw-bold text-success fs-1">
                                        S/ {{ number_format($productData['precio_con_descuento']['precio_final_local'], 2) }}
                                    </span>
                                    <span class="badge bg-success ms-2">
                                        {{ $productData['precio_con_descuento']['porcentaje'] }}% OFF
                                    </span>
                                </div>
                            @else
                                <div class="mb-3">
                                    <span class="fw-bold text-success fs-1">
                                        S/ {{ number_format($productData['precio_con_descuento']['precio_final_local'], 2) }}
                                    </span>
                                </div>
                            @endif

                            <!-- Lo que tienes que saber -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Lo que tienes que saber de este producto</h6>
                                <ul class="list-unstyled">
                                    @foreach($productData['caracteristicas'] as $key => $value)
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <strong>{{ $key }}:</strong> {{ $value }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Stock para productos físicos -->
                            @if($producto->esFisico() && $productData['stock_actual'] !== null)
                                <div class="mb-4">
                                    <span class="badge {{ $productData['stock_actual'] > 0 ? 'bg-success' : 'bg-danger' }} py-2 px-3">
                                        <i class="fas {{ $productData['stock_actual'] > 0 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                        {{ $productData['stock_actual'] > 0 ? $productData['stock_actual'] . ' disponibles' : 'Agotado' }}
                                    </span>
                                </div>
                            @endif

                            <!-- Botones de acción -->
                            <div class="d-grid gap-3">
                                @if($producto->esFisico() && $productData['stock_actual'] == 0)
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

            <!-- Productos relacionados -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tag me-2"></i>Más productos
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-secondary small mb-0">
                        Ver más productos de {{ $producto->categoria->nombre }}
                    </p>
                    <!-- Aquí puedes agregar productos relacionados -->
                </div>
            </div>
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

    /* Rating stars */
    .fa-star, .fa-star-half-alt {
        color: #ffc107;
    }
</style>

<!-- Script para cambiar imagen principal -->
<script>
function changeMainImage(imageUrl, element) {
    document.getElementById('mainProductImage').src = imageUrl;

    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.style.borderColor = 'transparent';
        item.classList.remove('active');
    });

    element.style.borderColor = '#065f46';
    element.classList.add('active');
}

// Script para agregar al carrito (igual que antes)
function agregarAlCarrito(productoId) {
    const button = event.currentTarget;
    const originalText = button.innerHTML;

    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Agregando...';
    button.disabled = true;

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

            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion('Producto agregado al carrito', 'success');
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
            window.mostrarNotificacion('Error al agregar el producto', 'danger');
        } else {
            alert('Error al agregar el producto');
        }
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>
@endsection
