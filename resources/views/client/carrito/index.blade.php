@extends('layouts.cliente.app')

@section('title', 'Mi Carrito de Compras')
@section('page-title', 'Carrito de Compras')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('producto.index') }}" class="text-success">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mi Carrito</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Columna principal: Items del carrito -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Mi Carrito ({{ $totales->total_items }} productos)
                    </h5>
                    @if($totales->total_items > 0)
                        <button class="btn btn-light btn-sm" onclick="vaciarCarrito()">
                            <i class="fas fa-trash-alt me-1"></i>
                            Vaciar carrito
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Precio</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        @php
                                            $producto = $item->producto;
                                            $esFisico = $producto && $producto->esFisico();
                                            $esDigital = $producto && $producto->esDigital();
                                            $stockDisponible = $esFisico ? ($producto->stock->cantidad ?? 0) : null;
                                        @endphp
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <!-- Imagen del producto -->
                                                    <div class="flex-shrink-0 me-3">
                                                        @php
                                                            $imagen = null;
                                                            for($i = 1; $i <= 5; $i++) {
                                                                $campo = "imagen_url_$i";
                                                                if($producto && $producto->$campo) {
                                                                    $imagen = $producto->getImageUrl($campo);
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        @if($imagen)
                                                            <img src="{{ $imagen }}"
                                                                 alt="{{ $producto->nombre }}"
                                                                 style="width: 60px; height: 60px; object-fit: cover;"
                                                                 class="rounded">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                                 style="width: 60px; height: 60px;">
                                                                <i class="fas fa-image text-secondary"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-1">{{ $producto->nombre ?? 'Producto no disponible' }}</h6>
                                                        <small class="text-secondary">
                                                            @if($producto)
                                                                <span class="badge {{ $esDigital ? 'bg-info' : 'bg-primary' }} me-1">
                                                                    <i class="fas {{ $esDigital ? 'fa-cloud' : 'fa-box' }} me-1"></i>
                                                                    {{ ucfirst($producto->tipo_producto) }}
                                                                </span>
                                                                @if($esFisico && $producto->sku)
                                                                    <span class="text-secondary">SKU: {{ $producto->sku }}</span>
                                                                @endif
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($item->aplica_descuento && $item->porcentaje_descuento > 0)
                                                    <div>
                                                        <span class="text-decoration-line-through text-secondary small">
                                                            S/ {{ number_format($item->precio_adquirido_local, 2) }}
                                                        </span>
                                                        <br>
                                                        <span class="fw-bold text-success">
                                                            S/ {{ number_format($item->precio_adquirido_local, 2) }}
                                                        </span>
                                                        <small class="badge bg-success ms-1">-{{ $item->porcentaje_descuento }}%</small>
                                                    </div>
                                                @else
                                                    <span class="fw-bold">S/ {{ number_format($item->precio_adquirido_local, 2) }}</span>
                                                @endif
                                                @if($item->precio_adquirido_usd)
                                                    <br>
                                                    <small class="text-secondary">${{ number_format($item->precio_adquirido_usd, 2) }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($esDigital)
                                                    <!-- Producto digital: cantidad fija -->
                                                    <span class="badge bg-info">1 unidad (digital)</span>
                                                @else
                                                    <!-- Producto físico: controles de cantidad -->
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <button class="btn btn-sm btn-outline-secondary"
                                                                onclick="actualizarCantidad({{ $item->id }}, {{ $item->cantidad - 1 }})"
                                                                {{ $item->cantidad <= 1 ? 'disabled' : '' }}>
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <span class="mx-2">{{ $item->cantidad }}</span>
                                                        <button class="btn btn-sm btn-outline-secondary"
                                                                onclick="actualizarCantidad({{ $item->id }}, {{ $item->cantidad + 1 }})"
                                                                {{ $esFisico && $stockDisponible && $item->cantidad >= $stockDisponible ? 'disabled' : '' }}>
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    @if($esFisico && $stockDisponible)
                                                        <small class="text-secondary d-block mt-1">
                                                            {{ $stockDisponible }} disponibles
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-success">
                                                S/ {{ number_format($item->subtotal_local, 2) }}
                                                @if($item->subtotal_usd)
                                                    <br>
                                                    <small class="text-secondary">${{ number_format($item->subtotal_usd, 2) }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-link text-danger p-0"
                                                        onclick="eliminarItem({{ $item->id }})"
                                                        title="Eliminar producto">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-secondary mb-3"></i>
                            <h5>Tu carrito está vacío</h5>
                            <p class="text-secondary">¿No sabes qué comprar? ¡Miles de productos te esperan!</p>
                            <a href="{{ route('producto.index') }}" class="btn btn-success">
                                <i class="fas fa-arrow-left me-2"></i>
                                Ir a la tienda
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna derecha: Resumen -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Resumen de compra
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal ({{ $totales->total_items }} productos):</span>
                        <span class="fw-bold">S/ {{ number_format($totales->subtotal_local, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Envío:</span>
                        <span class="text-success">Calcular después</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-success">S/ {{ number_format($totales->total_local, 2) }}</span>
                    </div>

                    @if($totales->total_usd > 0)
                        <p class="text-secondary small text-end">
                            ≈ ${{ number_format($totales->total_usd, 2) }} USD
                        </p>
                    @endif

                    @if($items->count() > 0)
                        <button class="btn btn-success btn-lg w-100 mb-2" onclick="procederAlPago()">
                            <i class="fas fa-arrow-right me-2"></i>
                            Proceder al pago
                        </button>
                        <button class="btn btn-outline-success w-100" onclick="seguirComprando()">
                            <i class="fas fa-arrow-left me-2"></i>
                            Seguir comprando
                        </button>
                    @endif
                </div>
            </div>

            <!-- Medios de pago -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Medios de pago
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <i class="fab fa-cc-visa fa-2x text-secondary"></i>
                        <i class="fab fa-cc-mastercard fa-2x text-secondary"></i>
                        <i class="fab fa-cc-amex fa-2x text-secondary"></i>
                        <i class="fab fa-cc-paypal fa-2x text-secondary"></i>
                        <i class="fas fa-money-bill fa-2x text-secondary"></i>
                    </div>
                    <p class="text-secondary small mt-2 mb-0">
                        <i class="fas fa-shield-alt me-1"></i>
                        Pago seguro garantizado
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para solicitar login -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark" id="loginRequiredModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Inicio de sesión requerido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-user-lock text-warning fa-4x mb-3"></i>
                <p class="mb-2">Para continuar con la compra, necesitas iniciar sesión.</p>
                <p class="text-secondary small">Si no tienes una cuenta, puedes registrarte en un minuto.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <a href="{{ route('client.login') }}?redirect={{ urlencode(request()->fullUrl()) }}"
                   class="btn btn-success">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Iniciar sesión
                </a>
                <a href="{{ route('client.register') }}?redirect={{ urlencode(request()->fullUrl()) }}"
                   class="btn btn-outline-success">
                    <i class="fas fa-user-plus me-2"></i>
                    Registrarme
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Seguir comprando
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
// Actualizar cantidad
function actualizarCantidad(itemId, nuevaCantidad) {
    if (nuevaCantidad < 1) return;

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/carrito/actualizar/${itemId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ cantidad: nuevaCantidad })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion(data.error || 'Error al actualizar cantidad', 'danger');
            } else {
                alert(data.error || 'Error al actualizar cantidad');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Error al actualizar cantidad', 'danger');
        } else {
            alert('Error al actualizar cantidad');
        }
    });
}

// Eliminar item
function eliminarItem(itemId) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch(`/carrito/eliminar/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion(data.error || 'Error al eliminar producto', 'danger');
            } else {
                alert(data.error || 'Error al eliminar producto');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Error al eliminar producto', 'danger');
        } else {
            alert('Error al eliminar producto');
        }
    });
}

// Vaciar carrito
function vaciarCarrito() {
    if (!confirm('¿Vaciar todo el carrito?')) return;

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/carrito/vaciar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Error al vaciar carrito', 'danger');
        } else {
            alert('Error al vaciar carrito');
        }
    });
}

// Proceder al pago con verificación de login
function procederAlPago() {
    @auth('client')
        // Usuario autenticado - ir directo al checkout
        window.location.href = '{{ route('checkout.index') }}';
    @else
        // Usuario no autenticado - mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        modal.show();
    @endauth
}

// Seguir comprando
function seguirComprando() {
    window.location.href = '{{ route('producto.index') }}';
}

// Actualizar sidebar del carrito
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.actualizarSidebarCarrito === 'function') {
        window.actualizarSidebarCarrito();
    }
});
</script>
@endsection
