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

    <div class="row">
        <div class="col-md-6">
            <!-- Carrusel de imágenes -->
            <div id="productoCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded-3 shadow">
                    @php
                        $images = [];
                        for($i = 1; $i <= 5; $i++) {
                            $imgField = "imagen_url_$i";
                            if($producto->$imgField) {
                                // Limpiar y formatear la URL de la imagen
                                $url = $producto->$imgField;

                                // Eliminar prefijos incorrectos
                                $url = str_replace('/producto/', '/', $url);
                                $url = str_replace('storage/storage/', 'storage/', $url);
                                $url = str_replace('/storage/storage/', '/storage/', $url);

                                // Si no es una URL completa, agregar asset()
                                if (!filter_var($url, FILTER_VALIDATE_URL) && !str_starts_with($url, 'http')) {
                                    // Asegurar que tenga el formato correcto
                                    if (str_starts_with($url, 'storage/')) {
                                        $url = asset($url);
                                    } elseif (str_starts_with($url, '/storage/')) {
                                        $url = asset(substr($url, 1));
                                    } else {
                                        $url = asset('storage/' . ltrim($url, '/'));
                                    }
                                }

                                $images[] = $url;
                            }
                        }
                    @endphp

                    @if(count($images) > 0)
                        @foreach($images as $index => $image)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                <img src="{{ $image }}"
                                     class="d-block w-100"
                                     alt="{{ $producto->nombre }} - Imagen {{ $index + 1 }}"
                                     style="height: 400px; object-fit: contain;"
                                     onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}';">
                            </div>
                        @endforeach
                    @else
                        <div class="carousel-item active">
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="height: 400px;">
                                <i class="fas fa-image fa-5x text-secondary"></i>
                            </div>
                        </div>
                    @endif
                </div>

                @if(count($images) > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#productoCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-success rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productoCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-success rounded-circle p-3" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h1 class="fw-bold mb-3">{{ $producto->nombre }}</h1>

                    <div class="mb-3">
                        <span class="badge bg-success bg-opacity-10 text-success py-2 px-3">
                            <i class="fas fa-folder me-2"></i>{{ $producto->categoria->nombre }}
                        </span>
                        <span class="badge {{ $producto->tipo_producto == 'digital' ? 'bg-info' : 'bg-primary' }} ms-2 py-2 px-3">
                            <i class="fas {{ $producto->tipo_producto == 'digital' ? 'fa-cloud' : 'fa-box' }} me-2"></i>
                            {{ ucfirst($producto->tipo_producto) }}
                        </span>
                    </div>

                    @if($producto->descripcion)
                        <p class="text-secondary mb-4">{{ $producto->descripcion }}</p>
                    @endif

                    <hr>

                    <div class="mb-4">
                        <h5 class="fw-bold">Precios:</h5>
                        @if($producto->precioUSD)
                            <p class="mb-2"><strong>USD:</strong> <span class="fs-3 fw-bold text-success">${{ number_format($producto->precioUSD, 2) }}</span></p>
                        @endif
                        @if($producto->precioLocal)
                            <p><strong>Local:</strong> <span class="fs-3 fw-bold text-success">${{ number_format($producto->precioLocal, 2) }}</span></p>
                        @endif
                    </div>

                    @if($producto->esFisico() && $producto->stock)
                        <div class="alert {{ $producto->stock->cantidad > 0 ? 'alert-success' : 'alert-danger' }} mb-4">
                            <i class="fas {{ $producto->stock->cantidad > 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-2"></i>
                            {{ $producto->stock->cantidad > 0 ? $producto->stock->cantidad . ' unidades disponibles' : 'Producto agotado' }}
                        </div>
                    @endif

                    @if($producto->esDigital() && $producto->url_recurso)
                        @php
                            $recursoUrl = $producto->url_recurso;
                            if (!filter_var($recursoUrl, FILTER_VALIDATE_URL) && !str_starts_with($recursoUrl, 'http')) {
                                $recursoUrl = asset('storage/' . ltrim($recursoUrl, '/'));
                            }
                        @endphp
                        <div class="mb-4">
                            <a href="{{ $recursoUrl }}" target="_blank" class="btn btn-info text-white">
                                <i class="fas fa-external-link-alt me-2"></i>Ver recurso digital
                            </a>
                        </div>
                    @endif

                    <div class="d-grid gap-3">
                        <button class="btn btn-success btn-lg" onclick="agregarAlCarrito({{ $producto->id }})">
                            <i class="fas fa-cart-plus me-2"></i>Agregar al carrito
                        </button>
                        <a href="{{ route('producto.index') }}" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Seguir comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para el carrito (temporal) -->
<script>
    function agregarAlCarrito(id) {
        // Aquí irá la lógica del carrito
        alert('Producto agregado al carrito (funcionalidad en desarrollo)');
        console.log('Producto ID:', id);
    }
</script>
@endsection
