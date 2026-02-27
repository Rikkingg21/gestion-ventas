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
                        @if(isset($moneda_actual))
                            <small class="ms-2">
                                <span class="badge bg-light text-success">
                                    {{ $moneda_actual->simbolo }} {{ $moneda_actual->codigo_iso }}
                                </span>
                            </small>
                        @endif
                    </h5>
                    @if($totales->total_items > 0)
                        <button class="btn btn-light btn-sm" onclick="vaciarCarrito()">
                            <i class="fas fa-trash-alt me-1"></i>
                            Vaciar carrito
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if(isset($itemsProcesados) && $itemsProcesados->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Precio Unit.</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemsProcesados as $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <!-- Imagen del producto -->
                                                    <div class="flex-shrink-0 me-3">
                                                        @if($item->imagen)
                                                            <img src="{{ $item->imagen }}"
                                                                 alt="{{ $item->nombre }}"
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
                                                        <h6 class="fw-bold mb-1">{{ $item->nombre }}</h6>
                                                        <small class="text-secondary">
                                                            <span class="badge {{ $item->es_digital ? 'bg-info' : 'bg-primary' }} me-1">
                                                                <i class="fas {{ $item->es_digital ? 'fa-cloud' : 'fa-box' }} me-1"></i>
                                                                {{ ucfirst($item->tipo_producto) }}
                                                            </span>
                                                            @if($item->es_fisico && $item->sku)
                                                                <span class="text-secondary">SKU: {{ $item->sku }}</span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($item->aplica_descuento && $item->porcentaje_descuento > 0)
                                                    <div>
                                                        <span class="text-decoration-line-through text-secondary small">
                                                            {{ $totales->moneda_actual->simbolo }}{{ number_format($item->precio_unitario_actual, 2) }}
                                                        </span>
                                                        <br>
                                                        <span class="fw-bold text-success">
                                                            {{ $totales->moneda_actual->simbolo }}{{ number_format($item->precio_unitario_actual, 2) }}
                                                        </span>
                                                        <small class="badge bg-success ms-1">-{{ $item->porcentaje_descuento }}%</small>
                                                    </div>
                                                @else
                                                    <span class="fw-bold">{{ $item->precio_unitario_actual_formateado }}</span>
                                                @endif

                                                <!-- Mostrar precio en USD como referencia si es diferente -->
                                                @if($totales->moneda_actual->codigo_iso != 'USD' && $item->precio_unitario_usd)
                                                    <br>
                                                    <small class="text-secondary">${{ number_format($item->precio_unitario_usd, 2) }} USD</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->es_digital)
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
                                                                {{ $item->es_fisico && $item->stock_disponible && $item->cantidad >= $item->stock_disponible ? 'disabled' : '' }}>
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    @if($item->es_fisico && $item->stock_disponible)
                                                        <small class="text-secondary d-block mt-1">
                                                            {{ $item->stock_disponible }} disponibles
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-success">
                                                {{ $item->subtotal_actual_formateado }}
                                                @if($totales->moneda_actual->codigo_iso != 'USD' && $item->subtotal_usd)
                                                    <br>
                                                    <small class="text-secondary">${{ number_format($item->subtotal_usd, 2) }} USD</small>
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
                        @if(isset($moneda_actual))
                            <small class="ms-2">
                                {{ $moneda_actual->codigo_iso }}
                            </small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal ({{ $totales->total_items }} productos):</span>
                        <span class="fw-bold">{{ $totales->subtotal_actual_formateado }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Envío:</span>
                        <span class="text-success">Calcular después</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-success">{{ $totales->total_actual_formateado }}</span>
                    </div>

                    <!-- Mostrar total en USD como referencia si es diferente -->
                    @if(isset($totales->moneda_actual) && $totales->moneda_actual->codigo_iso != 'USD' && $totales->total_usd > 0)
                        <p class="text-secondary small text-end">
                            ≈ ${{ number_format($totales->total_usd, 2) }} USD
                        </p>
                    @endif

                    @if(isset($itemsProcesados) && $itemsProcesados->count() > 0)
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

            <!-- Selector de moneda rápido (opcional) -->
            @if(isset($monedasDisponibles) && $monedasDisponibles->count() > 1)
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Cambiar moneda
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($monedasDisponibles as $moneda)
                            <button class="btn btn-sm {{ $monedaActual && $monedaActual->codigo_iso == $moneda->codigo_iso ? 'btn-success' : 'btn-outline-success' }}"
                                    onclick="cambiarMoneda('{{ $moneda->codigo_iso }}')">
                                {{ $moneda->simbolo }} {{ $moneda->codigo_iso }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

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

<!-- Modal para solicitar login (sin cambios) -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <!-- ... contenido del modal igual ... -->
</div>

<!-- Scripts actualizados -->
<script>
// Función para formatear precio (fallback)
function formatearPrecio(monto, simbolo = 'S/') {
    return `${simbolo} ${parseFloat(monto || 0).toFixed(2)}`;
}

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
            // Recargar para mostrar los cambios
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

// Cambiar moneda
function cambiarMoneda(currencyCode) {
    console.log('Cambiando moneda a:', currencyCode);

    fetch('/cambiar-moneda', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ currency: currencyCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recargar para mostrar precios en nueva moneda
            location.reload();
        } else {
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion('Error al cambiar la moneda', 'danger');
            } else {
                alert('Error al cambiar la moneda');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Error al cambiar la moneda', 'danger');
        } else {
            alert('Error al cambiar la moneda');
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
