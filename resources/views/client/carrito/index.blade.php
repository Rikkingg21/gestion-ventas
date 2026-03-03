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
                        <span id="cartHeaderTitle">Mi Carrito ({{ $totales->total_items }} productos)</span>
                        @if(isset($moneda_actual))
                            <small class="ms-2">
                                <span class="badge bg-light text-success">
                                    {{ $moneda_actual->simbolo }} {{ $moneda_actual->codigo_iso }}
                                </span>
                            </small>
                        @endif
                    </h5>
                    <button class="btn btn-light btn-sm" onclick="vaciarCarrito()" id="vaciarCarritoBtn" {{ $totales->total_items > 0 ? '' : 'style=display:none' }}>
                        <i class="fas fa-trash-alt me-1"></i>
                        Vaciar carrito
                    </button>
                </div>
                <div class="card-body p-0">
                    <!-- Contenedor principal que se actualizará vía AJAX -->
                    <div id="carrito-contenido-principal">
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
                                                                {{ $item->precio_unitario_actual_formateado }}
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
                                                            <span class="mx-2 cantidad-valor">{{ $item->cantidad }}</span>
                                                            <button class="btn btn-sm btn-outline-secondary btn-incrementar"
                                                                    onclick="actualizarCantidad({{ $item->id }}, {{ $item->cantidad + 1 }})"
                                                                    {{ $item->es_fisico && $item->stock_disponible && $item->cantidad >= $item->stock_disponible ? 'disabled' : '' }}>
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                        @if($item->es_fisico && $item->stock_disponible)
                                                            <small class="text-secondary d-block mt-1 stock-info">
                                                                {{ $item->stock_disponible }} disponibles
                                                            </small>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold text-success subtotal-cell">
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
                        <span class="text-secondary" id="subtotal-texto">Subtotal ({{ $totales->total_items }} productos):</span>
                        <span class="fw-bold" id="subtotal-valor">{{ $totales->subtotal_actual_formateado ?? ''}}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Envío:</span>
                        <span class="text-success">Calcular después</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-success" id="total-valor">{{ $totales->total_actual_formateado ?? '' }}</span>
                    </div>

                    <!-- Mostrar total en USD como referencia si es diferente -->
                    @if(isset($totales->moneda_actual) && $totales->moneda_actual->codigo_iso != 'USD' && $totales->total_usd > 0)
                        <p class="text-secondary small text-end" id="total-usd-ref">
                            ≈ ${{ number_format($totales->total_usd, 2) }} USD
                        </p>
                    @else
                        <p class="text-secondary small text-end" id="total-usd-ref" style="display: none;"></p>
                    @endif

                    <div id="botones-accion">
                        @if(isset($itemsProcesados) && $itemsProcesados->count() > 0)
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
@endsection

@push('scripts')
<script>
// Función para obtener el token CSRF (reutilizando la del carrito.js)
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) {
        console.error('CSRF token meta tag no encontrado');
        return null;
    }
    return meta.content;
}

// Función para formatear precio según la moneda (si no existe)
if (typeof window.formatearPrecio !== 'function') {
    window.formatearPrecio = function(monto, moneda) {
        if (!moneda) {
            return `$${parseFloat(monto || 0).toFixed(2)}`;
        }
        return `${moneda.simbolo}${parseFloat(monto || 0).toFixed(2)}`;
    };
}

// Actualizar cantidad
window.actualizarCantidad = function(itemId, nuevaCantidad) {
    if (nuevaCantidad < 1) return;

    const token = getCsrfToken();
    if (!token) {
        window.mostrarNotificacion?.('Error de seguridad: Token CSRF no disponible', 'danger');
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
            // Actualizar la UI con los datos recibidos
            actualizarUI(itemId, nuevaCantidad, data);

            // Actualizar sidebar si existe
            if (typeof window.actualizarSidebarCarrito === 'function') {
                window.actualizarSidebarCarrito();
            }

            window.mostrarNotificacion?.('Cantidad actualizada', 'success');
        } else {
            window.mostrarNotificacion?.(data.error || 'Error al actualizar cantidad', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.mostrarNotificacion?.('Error al actualizar cantidad', 'danger');
    });
};

// Función para actualizar la UI con los datos del servidor
function actualizarUI(itemId, nuevaCantidad, data) {
    // Buscar la fila del producto
    const fila = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (!fila) return;

    // Actualizar el span de cantidad
    const cantidadSpan = fila.querySelector('.cantidad-valor');
    if (cantidadSpan) {
        cantidadSpan.textContent = nuevaCantidad;
    }

    // Actualizar botones según estado
    const btnMinus = fila.querySelector('.btn-outline-secondary:first-child');
    const btnPlus = fila.querySelector('.btn-incrementar');

    if (btnMinus) {
        btnMinus.disabled = nuevaCantidad <= 1;
    }

    // Actualizar totales generales
    if (data.total_items !== undefined) {
        actualizarTotalesGenerales(data);
    }
}

// Función para actualizar los totales generales
function actualizarTotalesGenerales(data) {
    console.log('Actualizando totales con:', data);

    // Actualizar el contador en el header
    const headerTitle = document.getElementById('cartHeaderTitle');
    if (headerTitle && data.total_items !== undefined) {
        headerTitle.textContent = `Mi Carrito (${data.total_items} productos)`;
    }

    // Actualizar el texto del subtotal
    const subtotalTexto = document.getElementById('subtotal-texto');
    if (subtotalTexto && data.total_items !== undefined) {
        subtotalTexto.innerHTML = `Subtotal (${data.total_items} productos):`;
    }

    // Actualizar el valor del subtotal
    const subtotalValor = document.getElementById('subtotal-valor');
    if (subtotalValor && data.subtotal_actual_formateado) {
        subtotalValor.textContent = data.subtotal_actual_formateado;
    }

    // Actualizar el valor del total
    const totalValor = document.getElementById('total-valor');
    if (totalValor && data.total_actual_formateado) {
        totalValor.textContent = data.total_actual_formateado;
    }

    // Actualizar referencia USD
    const usdRef = document.getElementById('total-usd-ref');
    if (usdRef) {
        if (data.total_usd) {
            usdRef.innerHTML = `≈ $${parseFloat(data.total_usd).toFixed(2)} USD`;
            usdRef.style.display = 'block';
        } else {
            usdRef.style.display = 'none';
        }
    }

    // Mostrar/ocultar botón vaciar carrito
    const vaciarBtn = document.getElementById('vaciarCarritoBtn');
    if (vaciarBtn) {
        vaciarBtn.style.display = data.total_items > 0 ? 'block' : 'none';
    }

    // Actualizar botones de acción si el carrito quedó vacío
    if (data.total_items === 0) {
        mostrarCarritoVacio();
    }
}

// Eliminar item
window.eliminarItem = function(itemId) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;

    const token = getCsrfToken();
    if (!token) {
        window.mostrarNotificacion?.('Error de seguridad: Token CSRF no disponible', 'danger');
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
            // Eliminar la fila del DOM
            const fila = document.querySelector(`tr[data-item-id="${itemId}"]`);
            if (fila) {
                fila.remove();
            }

            // Actualizar totales generales
            if (data.totales) {
                actualizarTotalesGenerales(data.totales);
            }

            // Verificar si el carrito quedó vacío
            const tbody = document.querySelector('tbody');
            if (!tbody || tbody.children.length === 0) {
                mostrarCarritoVacio();
            }

            // Actualizar sidebar
            if (typeof window.actualizarSidebarCarrito === 'function') {
                window.actualizarSidebarCarrito();
            }

            window.mostrarNotificacion?.('Producto eliminado', 'success');
        } else {
            window.mostrarNotificacion?.(data.error || 'Error al eliminar producto', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.mostrarNotificacion?.('Error al eliminar producto', 'danger');
    });
};

// Vaciar carrito
window.vaciarCarrito = function() {
    if (!confirm('¿Vaciar todo el carrito?')) return;

    const token = getCsrfToken();
    if (!token) {
        window.mostrarNotificacion?.('Error de seguridad: Token CSRF no disponible', 'danger');
        return;
    }

    fetch('/carrito/vaciar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarCarritoVacio();

            // Actualizar sidebar
            if (typeof window.actualizarSidebarCarrito === 'function') {
                window.actualizarSidebarCarrito();
            }

            window.mostrarNotificacion?.('Carrito vaciado', 'success');
        } else {
            window.mostrarNotificacion?.('Error al vaciar carrito', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.mostrarNotificacion?.('Error al vaciar carrito', 'danger');
    });
};

// Función para mostrar el estado de carrito vacío
function mostrarCarritoVacio() {
    const contenedor = document.getElementById('carrito-contenido-principal');
    if (contenedor) {
        contenedor.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-secondary mb-3"></i>
                <h5>Tu carrito está vacío</h5>
                <p class="text-secondary">¿No sabes qué comprar? ¡Miles de productos te esperan!</p>
                <a href="{{ route('producto.index') }}" class="btn btn-success">
                    <i class="fas fa-arrow-left me-2"></i>
                    Ir a la tienda
                </a>
            </div>
        `;
    }

    // Ocultar botón de vaciar carrito
    const vaciarBtn = document.getElementById('vaciarCarritoBtn');
    if (vaciarBtn) {
        vaciarBtn.style.display = 'none';
    }

    // Ocultar botones de acción en el resumen
    const botonesAccion = document.getElementById('botones-accion');
    if (botonesAccion) {
        botonesAccion.innerHTML = '';
    }
}

// Cambiar moneda
window.cambiarMoneda = function(currencyCode) {
    console.log('Cambiando moneda a:', currencyCode);

    fetch('/cambiar-moneda', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ currency: currencyCode })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta:', data);
        if (data.success) {
            // Recargar la página para actualizar todos los precios
            location.reload();
        } else {
            window.mostrarNotificacion?.('Error al cambiar la moneda', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.mostrarNotificacion?.('Error al cambiar la moneda', 'danger');
    });
};

// Proceder al pago con verificación de login
window.procederAlPago = function() {
    @auth('client')
        window.location.href = '{{ route('checkout.index') }}';
    @else
        const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        modal.show();
    @endauth
};

// Seguir comprando
window.seguirComprando = function() {
    window.location.href = '{{ route('producto.index') }}';
};

// Función para mostrar notificaciones (si no existe globalmente)
if (typeof window.mostrarNotificacion !== 'function') {
    window.mostrarNotificacion = function(mensaje, tipo = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.style.maxWidth = '300px';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                <small>${mensaje}</small>
            </div>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    };
}

// Verificar que tenemos todos los IDs necesarios
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vista de carrito cargada');

    // Verificar elementos importantes
    const elementos = {
        headerTitle: document.getElementById('cartHeaderTitle'),
        subtotalTexto: document.getElementById('subtotal-texto'),
        subtotalValor: document.getElementById('subtotal-valor'),
        totalValor: document.getElementById('total-valor'),
        vaciarBtn: document.getElementById('vaciarCarritoBtn')
    };

    console.log('Elementos encontrados:', elementos);
});
</script>
@endpush
