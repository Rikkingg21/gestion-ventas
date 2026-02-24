@extends('layouts.cliente.app')

@section('title', $producto->nombre . ' - Mi Empresa')
@section('page-title', 'Detalle del Producto')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success">Inicio</a></li>
            <li class="breadcrumb-item"><a href="" class="text-success">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $producto->nombre }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Imágenes del producto -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Imagen principal -->
                    @if($producto->imagen_url_1)
                        <img src="{{ $producto->imagen_url_1 }}"
                             class="img-fluid rounded mb-3 main-image"
                             alt="{{ $producto->nombre }}"
                             id="mainImage"
                             style="width: 100%; height: 400px; object-fit: contain;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center rounded mb-3"
                             style="height: 400px;">
                            <i class="fas fa-image fa-5x text-secondary"></i>
                        </div>
                    @endif

                    <!-- Miniaturas -->
                    @if($producto->imagen_url_2 || $producto->imagen_url_3 || $producto->imagen_url_4 || $producto->imagen_url_5)
                        <div class="row g-2 mt-3">
                            @if($producto->imagen_url_1)
                                <div class="col-3">
                                    <img src="{{ $producto->imagen_url_1 }}"
                                         class="img-fluid rounded thumbnail active"
                                         onclick="cambiarImagen('{{ $producto->imagen_url_1 }}')"
                                         style="height: 80px; width: 100%; object-fit: cover; cursor: pointer;">
                                </div>
                            @endif
                            @for($i = 2; $i <= 5; $i++)
                                @php $imgUrl = "imagen_url_$i"; @endphp
                                @if($producto->$imgUrl)
                                    <div class="col-3">
                                        <img src="{{ $producto->$imgUrl }}"
                                             class="img-fluid rounded thumbnail"
                                             onclick="cambiarImagen('{{ $producto->$imgUrl }}')"
                                             style="height: 80px; width: 100%; object-fit: cover; cursor: pointer;">
                                    </div>
                                @endif
                            @endfor
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Información del producto -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <!-- Categoría -->
                    <div class="mb-3">
                        <span class="badge bg-success bg-opacity-10 text-success py-2 px-3">
                            <i class="fas fa-folder me-2"></i>
                            {{ $producto->categoria->nombre }}
                        </span>

                        <span class="badge {{ $producto->tipo_producto == 'digital' ? 'bg-info' : 'bg-primary' }} ms-2 py-2 px-3">
                            <i class="fas {{ $producto->tipo_producto == 'digital' ? 'fa-cloud' : 'fa-box' }} me-2"></i>
                            {{ ucfirst($producto->tipo_producto) }}
                        </span>
                    </div>

                    <!-- Nombre -->
                    <h1 class="fw-bold mb-3">{{ $producto->nombre }}</h1>

                    <!-- SKU -->
                    @if($producto->sku)
                        <p class="text-secondary mb-3">
                            <i class="fas fa-barcode me-2"></i>
                            SKU: <strong>{{ $producto->sku }}</strong>
                        </p>
                    @endif

                    <!-- Descripción -->
                    @if($producto->descripcion)
                        <div class="mb-4">
                            <h5 class="fw-bold">Descripción:</h5>
                            <p class="text-secondary">{{ $producto->descripcion }}</p>
                        </div>
                    @endif

                    <!-- Stock (para físicos) -->
                    @if($producto->esFisico() && $producto->stock)
                        <div class="mb-4">
                            <h5 class="fw-bold">Disponibilidad:</h5>
                            @if($producto->stock->cantidad > 0)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>{{ $producto->stock->cantidad }} unidades disponibles</strong>
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>Producto agotado</strong>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Recurso digital -->
                    @if($producto->esDigital() && $producto->url_recurso)
                        <div class="mb-4">
                            <h5 class="fw-bold">Recurso digital:</h5>
                            <a href="{{ $producto->url_recurso }}" target="_blank" class="btn btn-info text-white">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Ver recurso
                            </a>
                        </div>
                    @endif

                    <!-- Precios -->
                    <div class="mb-4">
                        <h5 class="fw-bold">Precios:</h5>
                        <div class="bg-light p-4 rounded-3">
                            @if($producto->precioUSD)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5">Precio USD:</span>
                                    <span class="fs-3 fw-bold text-success">${{ number_format($producto->precioUSD, 2) }}</span>
                                </div>
                            @endif

                            @if($producto->precioLocal)
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5">Precio Local:</span>
                                    <span class="fs-3 fw-bold text-success">${{ number_format($producto->precioLocal, 2) }}</span>
                                </div>
                            @endif

                            @if($producto->aplica_descuento)
                                <div class="mt-3">
                                    <span class="badge bg-warning text-dark py-2 px-3">
                                        <i class="fas fa-tag me-2"></i>
                                        Este producto aplica descuento
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-grid gap-3">
                        @if($producto->esFisico() && (!$producto->stock || $producto->stock->cantidad == 0))
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

                        <a href="" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Seguir comprando
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Información adicional:</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Producto 100% original
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Garantía de satisfacción
                        </li>
                        @if($producto->esFisico())
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Envío a todo el país
                            </li>
                        @else
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Descarga inmediata
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
    .main-image {
        transition: transform 0.3s;
    }

    .main-image:hover {
        transform: scale(1.05);
    }

    .thumbnail {
        opacity: 0.7;
        transition: opacity 0.3s, border 0.3s;
        border: 2px solid transparent;
    }

    .thumbnail:hover {
        opacity: 1;
    }

    .thumbnail.active {
        opacity: 1;
        border-color: #065f46;
    }

    .bg-success {
        background-color: #065f46 !important;
    }

    .text-success {
        color: #065f46 !important;
    }

    .btn-success {
        background-color: #065f46;
        border-color: #065f46;
    }

    .btn-success:hover {
        background-color: #047857;
        border-color: #047857;
    }

    .btn-outline-success {
        color: #065f46;
        border-color: #065f46;
    }

    .btn-outline-success:hover {
        background-color: #065f46;
        color: white;
    }
</style>

<!-- Script para cambiar imágenes -->
<script>
    function cambiarImagen(url) {
        document.getElementById('mainImage').src = url;

        // Actualizar clase active en miniaturas
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
            if(thumb.src === url) {
                thumb.classList.add('active');
            }
        });
    }

    function agregarAlCarrito(id) {
        // Aquí irá la lógica para agregar al carrito
        // Por ahora solo mostramos un mensaje
        alert('Producto agregado al carrito (funcionalidad en desarrollo)');
        console.log('Agregar al carrito producto ID:', id);
    }
</script>
@endsection
