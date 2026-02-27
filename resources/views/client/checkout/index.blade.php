@extends('layouts.cliente.app')

@section('title', 'Finalizar Compra')
@section('page-title', 'Checkout')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('producto.index') }}" class="text-success">Productos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('carrito.ver') }}" class="text-success">Carrito</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Columna principal: Formulario de pago -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-address-card me-2"></i>
                        Datos del Cliente
                    </h5>
                    @if(isset($monedaActual))
                        <span class="badge bg-light text-success">
                            <i class="fas fa-money-bill-wave me-1"></i>
                            {{ $monedaActual->simbolo }} {{ $monedaActual->codigo_iso }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres completos</label>
                            <input type="text" class="form-control"
                                value="{{ $user->nombres }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}"
                                readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control"
                                value="{{ $cliente->email ?? $user->email }}"
                                readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control"
                                value="{{ $user->tipo_documento }}: {{ $user->nro_documento }}"
                                readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control"
                                value="{{ $user->telefono }}"
                                readonly disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen del carrito -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Resumen del Pedido
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Precio Unit.</th>
                                    <th class="text-center">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemsProcesados as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2">
                                                    @if($item->imagen)
                                                        <img src="{{ $item->imagen }}" alt=""
                                                             style="width: 40px; height: 40px; object-fit: cover;"
                                                             class="rounded">
                                                    @else
                                                        <div class="bg-light rounded" style="width: 40px; height: 40px;"></div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="fw-bold small">{{ $item->nombre }}</span>
                                                    @if($item->aplica_descuento && $item->porcentaje_descuento > 0)
                                                        <br>
                                                        <small class="text-warning">
                                                            <i class="fas fa-tag me-1"></i>
                                                            -{{ $item->porcentaje_descuento }}%
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $item->es_digital ? 'bg-info' : 'bg-primary' }}">
                                                <i class="fas {{ $item->es_digital ? 'fa-cloud' : 'fa-box' }} me-1"></i>
                                                {{ ucfirst($item->tipo_producto) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item->cantidad }}</td>
                                        <td class="text-center">
                                            @if($item->aplica_descuento && $item->porcentaje_descuento > 0)
                                                <span class="text-decoration-line-through text-secondary small me-1">
                                                    {{ $totales->moneda_actual->simbolo }}{{ number_format($item->precio_unitario_actual * (1 + $item->porcentaje_descuento/100), 2) }}
                                                </span>
                                                <br>
                                                <span class="fw-bold text-success">{{ $item->precio_unitario_actual_formateado }}</span>
                                            @else
                                                <span class="fw-bold">{{ $item->precio_unitario_actual_formateado }}</span>
                                            @endif

                                            @if($totales->moneda_actual->codigo_iso != 'USD')
                                                <br>
                                                <small class="text-secondary">
                                                    ≈ ${{ number_format($item->precio_unitario_usd, 2) }} USD
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold text-success">
                                            {{ $item->subtotal_actual_formateado }}
                                            @if($totales->moneda_actual->codigo_iso != 'USD')
                                                <br>
                                                <small class="text-secondary">
                                                    ≈ ${{ number_format($item->subtotal_usd, 2) }} USD
                                                </small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Selector de moneda rápido (opcional) -->
            @if(isset($monedasDisponibles) && $monedasDisponibles->count() > 1)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Cambiar moneda de visualización
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
                    <small class="text-secondary d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Cambia la moneda para ver los precios en diferentes divisas.
                    </small>
                </div>
            </div>
            @endif

            <!-- Métodos de pago -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Selecciona tu método de pago
                    </h5>
                </div>
                <div class="card-body">
                    <form id="paymentForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Opciones de pago -->
                        <div class="row g-4 mb-4">
                            <!-- Yape -->
                            <div class="col-md-4">
                                <div class="payment-option card h-100" onclick="selectPaymentMethod('yape', this)">
                                    <div class="card-body text-center">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metodo_pago" id="yape" value="yape" required>
                                        </div>
                                        <img src="{{ asset('images/payment/yape-logo.png') }}" alt="Yape" style="height: 40px;" class="mb-2">
                                        <h6 class="fw-bold">Yape</h6>
                                        <p class="text-secondary small mb-0">Paga desde tu app Yape</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Plin -->
                            <div class="col-md-4">
                                <div class="payment-option card h-100" onclick="selectPaymentMethod('plin', this)">
                                    <div class="card-body text-center">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metodo_pago" id="plin" value="plin">
                                        </div>
                                        <img src="{{ asset('images/payment/plin-logo.png') }}" alt="Plin" style="height: 40px;" class="mb-2">
                                        <h6 class="fw-bold">Plin</h6>
                                        <p class="text-secondary small mb-0">Paga desde tu app Plin</p>
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal -->
                            <div class="col-md-4">
                                <div class="payment-option card h-100" onclick="selectPaymentMethod('paypal', this)">
                                    <div class="card-body text-center">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="metodo_pago" id="paypal" value="paypal">
                                        </div>
                                        <i class="fab fa-cc-paypal fa-3x text-primary mb-2"></i>
                                        <h6 class="fw-bold">PayPal</h6>
                                        <p class="text-secondary small mb-0">Paga con tu cuenta PayPal</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de datos según método de pago -->
                        <div id="yapeInfo" class="payment-info-section d-none">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-mobile-alt fa-2x me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Datos para Yape</h6>
                                        <p class="mb-0">Número: <strong>987 654 321</strong></p>
                                        <p class="mb-0">Nombre: <strong>Tienda Mi Empresa S.A.C.</strong></p>
                                        <p class="mb-0">Monto: <strong class="text-success">{{ $totales->total_actual_formateado }}</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adjunta el comprobante de Yape</label>
                                <input type="file" class="form-control" name="comprobante_yape" accept="image/*" id="yapeFile">
                                <small class="text-secondary">Captura de pantalla del comprobante (máx. 5MB)</small>
                            </div>
                        </div>

                        <div id="plinInfo" class="payment-info-section d-none">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-mobile-alt fa-2x me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Datos para Plin</h6>
                                        <p class="mb-0">Número: <strong>987 654 321</strong></p>
                                        <p class="mb-0">Nombre: <strong>Tienda Mi Empresa S.A.C.</strong></p>
                                        <p class="mb-0">Monto: <strong class="text-success">{{ $totales->total_actual_formateado }}</strong></p>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adjunta el comprobante de Plin</label>
                                <input type="file" class="form-control" name="comprobante_plin" accept="image/*" id="plinFile">
                                <small class="text-secondary">Captura de pantalla del comprobante (máx. 5MB)</small>
                            </div>
                        </div>

                        <div id="paypalInfo" class="payment-info-section d-none">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-paypal fa-2x me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Datos para PayPal</h6>
                                        <p class="mb-0">Email: <strong>pagos@tienda.com</strong></p>
                                        <p class="mb-0">Monto: <strong class="text-success">{{ $totales->total_actual_formateado }}</strong></p>
                                        <p class="mb-0">Serás redirigido a PayPal para completar el pago</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones adicionales -->
                        <div class="mb-3">
                            <label class="form-label">Observaciones (opcional)</label>
                            <textarea class="form-control" name="comentarios" rows="2" placeholder="Algún detalle adicional sobre tu compra..."></textarea>
                        </div>

                        <!-- Términos y condiciones -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="terminos" id="terminos" value="1" required>
                            <label class="form-check-label" for="terminos">
                                He leído y acepto los <a href="#" class="text-success">términos y condiciones</a> y la <a href="#" class="text-success">política de privacidad</a>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Resumen y acciones -->
        <div class="col-lg-4">
            <!-- Resumen de compra -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Resumen
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal ({{ $totales->total_items }} productos):</span>
                        <span class="fw-bold">{{ $totales->subtotal_actual_formateado }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Envío:</span>
                        <span class="text-success">Por calcular</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total a pagar:</span>
                        <span class="fw-bold fs-5 text-success">{{ $totales->total_actual_formateado }}</span>
                    </div>

                    <!-- Mostrar total en USD como referencia -->
                    @if($totales->moneda_actual->codigo_iso != 'USD')
                        <p class="text-secondary small text-end mb-3">
                            ≈ ${{ number_format($totales->total_usd, 2) }} USD
                        </p>
                    @endif

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Tu pedido será procesado una vez que confirmemos el pago. Recibirás un email con los detalles.</small>
                    </div>

                    <button type="button" class="btn btn-success btn-lg w-100 mb-2" onclick="enviarSolicitudPago()" id="submitBtn">
                        <i class="fas fa-check-circle me-2"></i>
                        Confirmar y solicitar pago
                    </button>
                    <a href="{{ route('carrito.ver') }}" class="btn btn-outline-success w-100">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver al carrito
                    </a>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-headset text-success me-2"></i>
                        ¿Necesitas ayuda?
                    </h6>
                    <p class="text-secondary small mb-2">
                        Si tienes problemas con el pago, contáctanos:
                    </p>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fab fa-whatsapp text-success me-2"></i>
                            WhatsApp: +51 987 654 321
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope text-success me-2"></i>
                            Email: soporte@tienda.com
                        </li>
                        <li>
                            <i class="fas fa-clock text-success me-2"></i>
                            Horario: Lun - Vie 9am - 6pm
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Solicitud enviada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h6>¡Solicitud de pago enviada!</h6>
                <p class="text-secondary mb-0" id="modalMessage">Hemos recibido tu solicitud. Te contactaremos cuando confirmemos el pago.</p>
                <p class="text-secondary small mt-2">Recibirás un email con el número de seguimiento.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('producto.index') }}" class="btn btn-success" id="seguirComprandoBtn">
                    <i class="fas fa-store me-2"></i>
                    Seguir comprando
                </a>
                <a href="{{ route('compras.index') }}" class="btn btn-outline-success" id="verComprasBtn">
                    <i class="fas fa-box me-2"></i>
                    Ver mis compras
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
.payment-option {
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.payment-option:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #065f46;
}

.payment-option.selected {
    border-color: #065f46 !important;
    background-color: #f0fdf4;
}

.payment-option .form-check-input:checked {
    background-color: #065f46;
    border-color: #065f46;
}

.loader {
    display: inline-block;
    width: 1.5rem;
    height: 1.5rem;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<!-- Scripts -->
<!-- Scripts actualizados -->
<script>
let metodoPagoSeleccionado = null;
let selectedElement = null;

// Función para cambiar moneda (usa la función global de carrito.js)
function cambiarMoneda(currencyCode) {
    if (typeof window.cambiarMoneda === 'function') {
        window.cambiarMoneda(currencyCode);
    } else {
        console.error('Función cambiarMoneda no disponible');
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
                location.reload();
            } else {
                alert('Error al cambiar la moneda');
            }
        });
    }
}

function selectPaymentMethod(metodo, element) {
    // Actualizar radio button
    document.getElementById(metodo).checked = true;

    // Remover clase selected de todas las opciones
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.classList.remove('selected');
    });

    // Agregar clase selected a la opción seleccionada
    element.classList.add('selected');
    selectedElement = element;

    // Ocultar todas las secciones de información
    document.querySelectorAll('.payment-info-section').forEach(section => {
        section.classList.add('d-none');
    });

    // Mostrar la sección correspondiente
    document.getElementById(metodo + 'Info').classList.remove('d-none');

    metodoPagoSeleccionado = metodo;
}

function enviarSolicitudPago() {
    // ... (misma función que antes, sin cambios) ...
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay método de pago seleccionado por defecto
    const radios = document.querySelectorAll('input[name="metodo_pago"]');
    radios.forEach(radio => {
        if (radio.checked) {
            const parentCard = radio.closest('.payment-option');
            if (parentCard) {
                selectPaymentMethod(radio.value, parentCard);
            }
        }
    });
});
</script>
@endsection
