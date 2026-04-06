{{-- resources/views/client/carrito/index.blade.php --}}
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

    <!-- Alerta si la moneda no acepta pagos -->
    @if(isset($aceptaPagos) && !$aceptaPagos && isset($monedaACobrar))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Información:</strong> La moneda {{ $monedaActual->nombre }} ({{ $monedaActual->simbolo }}) no acepta pagos directamente.
            Los precios se muestran en <strong>{{ $monedaActual->nombre }} ({{ $monedaActual->simbolo }})</strong> para referencia,
            pero el cobro se realizará en <strong>{{ $monedaACobrar->nombre }} ({{ $monedaACobrar->simbolo }})</strong>.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Columna principal: Items del carrito -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        <span id="cartHeaderTitle">Mi Carrito
                            @if(isset($totales) && $totales['total_items'] > 0)
                                ({{ $totales['total_items'] }} productos)
                            @else
                                (0 productos)
                            @endif
                        </span>
                        @if(isset($monedaActual))
                            <small class="ms-2">
                                <span class="badge bg-light text-success">
                                    {{ $monedaActual->simbolo }} {{ $monedaActual->codigo_iso }}
                                </span>
                            </small>
                        @endif
                    </h5>
                    <button class="btn btn-light btn-sm" onclick="vaciarCarrito()" id="vaciarCarritoBtn"
                            {{ isset($totales) && $totales['total_items'] > 0 ? '' : 'style=display:none' }}>
                        <i class="fas fa-trash-alt me-1"></i>
                        Vaciar carrito
                    </button>
                </div>
                <div class="card-body p-0">
                    <!-- Contenedor principal que se actualizará vía AJAX -->
                    <div id="carrito-contenido-principal">
                        @if(isset($itemsProcesados) && count($itemsProcesados) > 0)
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
                                            <tr data-item-id="{{ $item['id'] }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <!-- Imagen del producto -->
                                                        <div class="flex-shrink-0 me-3">
                                                            @if($item['imagen'])
                                                                <img src="{{ $item['imagen'] }}"
                                                                     alt="{{ $item['nombre'] }}"
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
                                                            <h6 class="fw-bold mb-1">{{ $item['nombre'] }}</h6>
                                                            <small class="text-secondary">
                                                                <span class="badge {{ $item['es_digital'] ? 'bg-info' : 'bg-primary' }} me-1">
                                                                    <i class="fas {{ $item['es_digital'] ? 'fa-cloud' : 'fa-box' }} me-1"></i>
                                                                    {{ ucfirst($item['tipo_producto']) }}
                                                                </span>
                                                                @if($item['es_fisico'] && $item['sku'])
                                                                    <span class="text-secondary">SKU: {{ $item['sku'] }}</span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($item['aplica_descuento'] && $item['porcentaje_descuento'] > 0)
                                                        @php
                                                            $precioOriginal = $item['precio_unitario_local'] / (1 - ($item['porcentaje_descuento'] / 100));
                                                        @endphp
                                                        <div>
                                                            <span class="text-decoration-line-through text-secondary small">
                                                                {{ $totales['moneda_actual']['simbolo'] }}{{ number_format($precioOriginal, 2) }}
                                                            </span>
                                                            <br>
                                                            <span class="fw-bold text-success">
                                                                {{ $totales['moneda_actual']['simbolo'] }}{{ number_format($item['precio_mostrar'], 2) }}
                                                            </span>
                                                            <small class="badge bg-success ms-1">-{{ $item['porcentaje_descuento'] }}%</small>
                                                        </div>
                                                    @else
                                                        <span class="fw-bold">
                                                            {{ $totales['moneda_actual']['simbolo'] }}{{ number_format($item['precio_mostrar'], 2) }}
                                                        </span>
                                                    @endif

                                                    <!-- Mostrar precio en USD si no acepta pagos -->
                                                    @if(isset($aceptaPagos) && !$aceptaPagos && $totales['moneda_actual']['codigo'] != 'USD')
                                                        <br>
                                                        <small class="text-secondary">
                                                            ≈ ${{ number_format($item['precio_unitario_usd'], 2) }} USD
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($item['es_digital'])
                                                        <span class="badge bg-info">1 unidad (digital)</span>
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <button class="btn btn-sm btn-outline-secondary btn-decrementar"
                                                                    onclick="actualizarCantidadItem({{ $item['id'] }}, {{ $item['cantidad'] - 1 }})"
                                                                    {{ $item['cantidad'] <= 1 ? 'disabled' : '' }}>
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                            <span class="mx-2 cantidad-valor">{{ $item['cantidad'] }}</span>
                                                            <button class="btn btn-sm btn-outline-secondary btn-incrementar"
                                                                    onclick="actualizarCantidadItem({{ $item['id'] }}, {{ $item['cantidad'] + 1 }})"
                                                                    {{ $item['es_fisico'] && $item['stock_disponible'] && $item['cantidad'] >= $item['stock_disponible'] ? 'disabled' : '' }}>
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                        @if($item['es_fisico'] && $item['stock_disponible'])
                                                            <small class="text-secondary d-block mt-1 stock-info">
                                                                {{ $item['stock_disponible'] }} disponibles
                                                            </small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold text-success subtotal-cell">
                                                    {{ $totales['moneda_actual']['simbolo'] }}{{ number_format($item['precio_mostrar'] * $item['cantidad'], 2) }}
                                                    @if(isset($aceptaPagos) && !$aceptaPagos && $totales['moneda_actual']['codigo'] != 'USD')
                                                        <br>
                                                        <small class="text-secondary">
                                                            ≈ ${{ number_format($item['precio_unitario_usd'] * $item['cantidad'], 2) }} USD
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-link text-danger p-0"
                                                            onclick="eliminarItemCarrito({{ $item['id'] }})"
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
        </div>

        <!-- Columna derecha: Resumen -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Resumen de compra
                        @if(isset($aceptaPagos) && !$aceptaPagos && isset($monedaACobrar))
                            <small class="ms-2">
                                <i class="fas fa-info-circle"></i> Pago en {{ $monedaACobrar->codigo_iso }}
                            </small>
                        @elseif(isset($monedaActual))
                            <small class="ms-2">
                                {{ $monedaActual->codigo_iso }}
                            </small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary" id="subtotal-texto">
                            Subtotal ({{ $totales['total_items'] ?? 0 }} productos):
                        </span>
                        <span class="fw-bold" id="subtotal-valor">
                            {{ $totales['moneda_actual']['simbolo'] ?? '$' }}{{ number_format($totales['total_actual'] ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Envío:</span>
                        <span class="text-success">Calcular después</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-success" id="total-valor">
                            {{ $totales['moneda_actual']['simbolo'] ?? '$' }}{{ number_format($totales['total_actual'] ?? 0, 2) }}
                        </span>
                    </div>

                    <!-- Mostrar total en USD si no acepta pagos -->
                    @if(isset($aceptaPagos) && !$aceptaPagos && isset($totales['total_usd']) && $totales['total_usd'] > 0)
                        <p class="text-secondary small text-end" id="total-moneda-original">
                            ≈ ${{ number_format($totales['total_usd'], 2) }} USD (monto a cobrar)
                        </p>
                    @endif

                    <div id="botones-accion">
                        @if(isset($itemsProcesados) && count($itemsProcesados) > 0)
                            <button class="btn btn-success btn-lg w-100 mb-2" onclick="procederAlPago()" id="btn-pagar">
                                <i class="fas fa-arrow-right me-2"></i>
                                Proceder al pago
                            </button>
                            <button class="btn btn-outline-success w-100" onclick="seguirComprando()" id="btn-seguir-comprando">
                                <i class="fas fa-arrow-left me-2"></i>
                                Seguir comprando
                            </button>
                        @endif
                    </div>
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
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="loginRequiredModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ¡Atención!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">Debes iniciar sesión para proceder al pago.</p>
                <a href="{{ route('client.login') }}" class="btn btn-danger me-2">
                    <i class="fas fa-sign-in-alt me-1"></i>
                    Iniciar sesión
                </a>
                <a href="{{ route('client.register') }}" class="btn btn-outline-danger">
                    <i class="fas fa-user-plus me-1"></i>
                    Registrarse
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Datos de moneda actual desde PHP
const monedaActualData = @json($monedaActual ?? null);
const aceptaPagos = @json($aceptaPagos ?? false);
const monedaInfo = monedaActualData ? {
    id: monedaActualData.id,
    codigo: monedaActualData.codigo_iso,
    simbolo: monedaActualData.simbolo,
    nombre: monedaActualData.nombre,
    tasa_cambio_usd: monedaActualData.tasa_cambio_usd || 1,
    acepta_pagos: aceptaPagos
} : { codigo: 'USD', simbolo: '$', nombre: 'Dólar Americano', tasa_cambio_usd: 1, acepta_pagos: true };

// Función para formatear precio
function formatearPrecioLocal(monto, moneda = monedaInfo) {
    if (!moneda) {
        return `$${parseFloat(monto || 0).toFixed(2)}`;
    }
    return `${moneda.simbolo}${parseFloat(monto || 0).toFixed(2)}`;
}

// Función para obtener token CSRF
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) {
        console.error('CSRF token meta tag no encontrado');
        return null;
    }
    return meta.content;
}

// Actualizar cantidad
function actualizarCantidadItem(itemId, nuevaCantidad) {
    if (nuevaCantidad < 1) return;

    const token = getCsrfToken();
    if (!token) {
        mostrarNotificacion('Error de seguridad: Token CSRF no disponible', 'danger');
        return;
    }

    fetch(`/carrito/actualizar-item/${itemId}`, {
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
            // Recargar la página para actualizar todos los datos
            location.reload();
        } else {
            mostrarNotificacion(data.error || 'Error al actualizar cantidad', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al actualizar cantidad', 'danger');
    });
}

window.eliminarItemCarrito = function(itemId) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;

    const token = getCsrfToken();
    if (!token) {
        mostrarNotificacion('Error de seguridad: Token CSRF no disponible', 'danger');
        return;
    }

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
            mostrarNotificacion(data.error || 'Error al eliminar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar producto', 'danger');
    });
};

window.vaciarCarrito = function() {
    if (!confirm('¿Vaciar todo el carrito? Esta acción no se puede deshacer.')) return;

    const token = getCsrfToken();
    if (!token) {
        mostrarNotificacion('Error de seguridad: Token CSRF no disponible', 'danger');
        return;
    }

    fetch('/carrito/vaciar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            mostrarNotificacion('Error al vaciar carrito', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al vaciar carrito', 'danger');
    });
};

window.procederAlPago = function() {
    @auth('client')
        window.location.href = '{{ route('checkout.index') }}';
    @else
        const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        modal.show();
    @endauth
};

window.seguirComprando = function() {
    window.location.href = '{{ route('producto.index') }}';
};

window.mostrarNotificacion = function(mensaje, tipo = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.maxWidth = '350px';
    alertDiv.style.minWidth = '250px';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            <div class="flex-grow-1">${mensaje}</div>
            <button type="button" class="btn-close btn-sm ms-2" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        if (alertDiv && alertDiv.remove) {
            alertDiv.remove();
        }
    }, 3000);
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Vista de carrito principal cargada');
    console.log('Moneda actual:', monedaInfo);
    console.log('Acepta pagos:', aceptaPagos);

    @if(session('error'))
        mostrarNotificacion('{{ session('error') }}', 'danger');
    @endif

    @if(session('success'))
        mostrarNotificacion('{{ session('success') }}', 'success');
    @endif
});
</script>
@endsection
