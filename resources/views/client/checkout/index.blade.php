@extends('layouts.cliente.app')

@section('title', 'Finalizar Compra')
@section('page-title', 'Checkout')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('producto.index') }}" class="text-primary">Productos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('carrito.ver') }}" class="text-primary">Carrito</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
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
        <!-- Columna principal: Formulario de pago -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-address-card me-2"></i>
                        Datos del Cliente
                    </h5>
                    @if(isset($monedaActual))
                        <span class="badge bg-light text-primary">
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
                <div class="card-header bg-primary text-white">
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
                                                @php
                                                    $precioOriginal = $item->precio_mostrar / (1 - ($item->porcentaje_descuento / 100));
                                                @endphp
                                                <span class="text-decoration-line-through text-secondary small me-1">
                                                    {{ $totales->moneda_actual->simbolo }}{{ number_format($precioOriginal, 2) }}
                                                </span>
                                                <br>
                                                <span class="fw-bold text-primary">
                                                    {{ $totales->moneda_actual->simbolo }}{{ number_format($item->precio_mostrar, 2) }}
                                                </span>
                                            @else
                                                <span class="fw-bold">
                                                    {{ $totales->moneda_actual->simbolo }}{{ number_format($item->precio_mostrar, 2) }}
                                                </span>
                                            @endif

                                            @if($totales->moneda_actual->codigo_iso != 'USD')
                                                <br>
                                                <small class="text-secondary">
                                                    ≈ ${{ number_format($item->precio_unitario_usd, 2) }} USD
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold text-primary">
                                            {{ $totales->moneda_actual->simbolo }}{{ number_format($item->subtotal_mostrar, 2) }}
                                            @if($totales->moneda_actual->codigo_iso != 'USD')
                                                <br>
                                                <small class="text-secondary">
                                                    ≈ ${{ number_format($item->precio_unitario_usd * $item->cantidad, 2) }} USD
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

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tag me-2"></i>
                        ¿Tienes un cupón de descuento?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="cuponInput"
                                placeholder="Ingresa tu código de cupón"
                                value="{{ $totales->cupon_aplicado['codigo'] ?? '' }}"
                                {{ isset($totales->cupon_aplicado) ? 'disabled' : '' }}>
                        </div>
                        <div class="col-md-4">
                            @if(isset($totales->cupon_aplicado))
                                <button class="btn btn-outline-danger w-100" onclick="quitarCupon()" id="quitarCuponBtn">
                                    <i class="fas fa-times me-2"></i>
                                    Quitar cupón
                                </button>
                            @else
                                <button class="btn btn-primary w-100" onclick="aplicarCupon()" id="aplicarCuponBtn">
                                    <i class="fas fa-check me-2"></i>
                                    Aplicar
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Mensaje de cupón aplicado -->
                    @if(isset($totales->cupon_aplicado))
                        <div class="alert alert-success mt-3 mb-0" id="cuponMensaje">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    <strong>Cupón "{{ $totales->cupon_aplicado['codigo'] }}" aplicado!</strong><br>
                                    <small>Descuento: {{ $totales->descuento_aplicado_formateado }}</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mt-3 mb-0" id="cuponInfo" style="display: none;"></div>
                        <div class="alert alert-danger mt-3 mb-0" id="cuponError" style="display: none;"></div>
                    @endif
                </div>
            </div>

            <!-- Métodos de pago -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
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
                            @forelse($metodosPagoData as $metodo)
                                <div class="col-md-4">
                                    <div class="payment-option card h-100" onclick="selectPaymentMethod('{{ $metodo['slug'] }}', this)">
                                        <div class="card-body text-center">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="metodo_pago"
                                                    id="{{ $metodo['slug'] }}"
                                                    value="{{ $metodo['slug'] }}"
                                                    required>
                                            </div>

                                            @if($metodo['imagen_url'])
                                                <img src="{{ asset($metodo['imagen_url']) }}"
                                                    alt="{{ $metodo['nombre'] }}"
                                                    style="height: 40px;"
                                                    class="mb-2">
                                            @elseif($metodo['icono_class'])
                                                <i class="{{ $metodo['icono_class'] }} fa-3x mb-2"></i>
                                            @endif

                                            <h6 class="fw-bold">{{ $metodo['nombre'] }}</h6>
                                            <p class="text-secondary small mb-0">
                                                Paga con {{ $metodo['nombre'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-warning text-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No hay métodos de pago disponibles
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <!-- Sección de datos según método de pago -->
                        @foreach($metodosPagoData as $metodo)
                            <div id="{{ $metodo['slug'] }}Info" class="payment-info-section d-none">
                                @php
                                    $detalles = $metodo['detalles'];
                                @endphp

                                <!-- Datos de la cuenta para pagar -->
                                <div class="alert alert-info mb-4">
                                    <div class="d-flex align-items-start">
                                        <i class="fas {{ $metodo['icono_class'] ?? 'fa-credit-card' }} fa-2x me-3"></i>
                                        <div class="w-100">
                                            <h6 class="fw-bold mb-2">Datos para realizar el pago</h6>

                                            <!-- BILLETERA DIGITAL (Yape, Plin) -->
                                            @if($metodo['tipo'] == 'billetera_digital')
                                                <div class="row">
                                                    @if(isset($detalles['numero']))
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-secondary">Número:</small><br>
                                                            <strong class="fs-5">{{ $detalles['numero'] }}</strong>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copiarTexto('{{ $detalles['numero'] }}')">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @if(isset($detalles['titular']))
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-secondary">Titular:</small><br>
                                                            <strong>{{ $detalles['titular'] }}</strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- CUENTA BANCARIA (BCP, Interbank, BBVA) -->
                                            @if($metodo['tipo'] == 'cuenta_bancaria')
                                                <div class="row">
                                                    @if(isset($detalles['banco']))
                                                        <div class="col-md-12 mb-2">
                                                            <small class="text-secondary">Banco:</small><br>
                                                            <strong>{{ $detalles['banco'] }}</strong>
                                                        </div>
                                                    @endif
                                                    @if(isset($detalles['n_cuenta']))
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-secondary">Número de cuenta:</small><br>
                                                            <strong class="fs-5">{{ $detalles['n_cuenta'] }}</strong>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copiarTexto('{{ $detalles['n_cuenta'] }}')">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @if(isset($detalles['cci']))
                                                        <div class="col-md-6 mb-2">
                                                            <small class="text-secondary">CCI:</small><br>
                                                            <strong class="fs-6">{{ $detalles['cci'] }}</strong>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copiarTexto('{{ $detalles['cci'] }}')">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @if(isset($detalles['titular']))
                                                        <div class="col-md-12 mb-2">
                                                            <small class="text-secondary">Titular:</small><br>
                                                            <strong>{{ $detalles['titular'] }}</strong>
                                                        </div>
                                                    @endif
                                                    @if(isset($detalles['tipo_cuenta']))
                                                        <div class="col-md-12 mb-2">
                                                            <small class="text-secondary">Tipo de cuenta:</small><br>
                                                            <span class="badge bg-primary">{{ $detalles['tipo_cuenta'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- TRANSFERENCIA POR EMAIL (PayPal) -->
                                            @if($metodo['tipo'] == 'transferencia_email')
                                                @if(isset($detalles['email']))
                                                    <div class="mb-2">
                                                        <small class="text-secondary">Email para enviar el pago:</small><br>
                                                        <strong class="fs-5">{{ $detalles['email'] }}</strong>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="copiarTexto('{{ $detalles['email'] }}')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                                @if(isset($detalles['instrucciones']))
                                                    <div class="mt-2">
                                                        <small class="text-secondary">Instrucciones:</small><br>
                                                        <span class="text-muted">{{ $detalles['instrucciones'] }}</span>
                                                    </div>
                                                @endif
                                            @endif

                                            <!-- MONTO A PAGAR (todos los métodos) -->
                                            <div class="mt-3 pt-2 border-top">
                                                <p class="mb-0 text-primary">
                                                    <i class="fas fa-money-bill-wave me-1"></i>
                                                    <strong>Monto a pagar:</strong> {{ $totales->total_actual_formateado }}
                                                    @if(!$aceptaPagos && isset($monedaACobrar))
                                                        <br>
                                                        <small>(Se cobrará en {{ $monedaACobrar->simbolo }} {{ $monedaACobrar->codigo_iso }})</small>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campos del formulario - TODOS los métodos tienen comprobantes -->
                                <div class="campos-formulario">
                                    <h6 class="fw-bold mb-3">Información del pago</h6>

                                    @foreach($metodo['campos_formulario'] as $campo)
                                        <div class="mb-3">
                                            <label class="form-label">
                                                {{ $campo['label'] }}
                                                @if($campo['required'])
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            @if($campo['tipo'] === 'file')
                                                <input type="file"
                                                    class="form-control"
                                                    name="{{ $campo['nombre_campo'] }}"
                                                    accept="{{ $campo['accept'] ?? 'image/*' }}"
                                                    id="{{ $campo['nombre_campo'] }}"
                                                    {{ $campo['required'] ? 'required' : '' }}>
                                                @if(isset($campo['help_text']))
                                                    <small class="text-secondary">{{ $campo['help_text'] }}</small>
                                                @endif
                                                @if(strpos($campo['nombre_campo'], 'comprobante') !== false)
                                                    <div class="mt-2" id="preview-{{ $campo['nombre_campo'] }}"></div>
                                                @endif
                                            @elseif($campo['tipo'] === 'email')
                                                <input type="email"
                                                    class="form-control"
                                                    name="{{ $campo['nombre_campo'] }}"
                                                    placeholder="{{ $campo['placeholder'] ?? '' }}"
                                                    id="{{ $campo['nombre_campo'] }}"
                                                    {{ $campo['required'] ? 'required' : '' }}>
                                            @else
                                                <input type="text"
                                                    class="form-control"
                                                    name="{{ $campo['nombre_campo'] }}"
                                                    placeholder="{{ $campo['placeholder'] ?? '' }}"
                                                    id="{{ $campo['nombre_campo'] }}"
                                                    {{ $campo['required'] ? 'required' : '' }}>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <!-- Observaciones adicionales -->
                        <div class="mb-3 mt-4">
                            <label class="form-label">Observaciones (opcional)</label>
                            <textarea class="form-control" name="comentarios" rows="2"
                                    placeholder="Algún detalle adicional sobre tu compra..."></textarea>
                        </div>

                        <!-- Términos y condiciones -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="terminos" id="terminos" value="1" required>
                            <label class="form-check-label" for="terminos">
                                He leído y acepto los <a href="#" class="text-primary">términos y condiciones</a> y la
                                <a href="#" class="text-primary">política de privacidad</a>
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
                <div class="card-header bg-primary text-white">
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

                    <!-- Descuento por cupón (si aplica) -->
                    @if(isset($totales->cupon_aplicado))
                        <div class="d-flex justify-content-between mb-2 text-primary">
                            <span class="text-secondary">Descuento cupón "{{ $totales->cupon_aplicado['codigo'] }}":</span>
                            <span class="fw-bold">-{{ $totales->descuento_aplicado_formateado }}</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Envío:</span>
                        <span class="text-primary">Por calcular</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total a pagar:</span>
                        <span class="fw-bold fs-5 text-primary">{{ $totales->total_actual_formateado }}</span>
                    </div>

                    <!-- Mostrar total en USD como referencia si la moneda actual no es USD -->
                    @if($totales->moneda_actual->codigo_iso != 'USD')
                        <p class="text-secondary small text-end mb-3">
                            ≈ ${{ number_format($totales->total_usd, 2) }} USD
                            @if(!$aceptaPagos)
                                <br>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Este es el monto que se cobrará
                                </span>
                            @endif
                        </p>
                    @endif

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Tu pedido será procesado una vez que confirmemos el pago. Recibirás un email con los detalles.</small>
                    </div>

                    <button type="button" class="btn btn-primary btn-lg w-100 mb-2" onclick="enviarSolicitudPago()" id="submitBtn">
                        <i class="fas fa-check-circle me-2"></i>
                        Confirmar y solicitar pago
                    </button>
                    <a href="{{ route('carrito.ver') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver al carrito
                    </a>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-headset text-primary me-2"></i>
                        ¿Necesitas ayuda?
                    </h6>
                    <p class="text-secondary small mb-2">
                        Si tienes problemas con el pago, contáctanos:
                    </p>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fab fa-whatsapp text-primary me-2"></i>
                            WhatsApp: +51 987 654 321
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            Email: soporte@tienda.com
                        </li>
                        <li>
                            <i class="fas fa-clock text-primary me-2"></i>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Solicitud enviada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-primary fa-4x mb-3"></i>
                <h6>¡Solicitud de pago enviada!</h6>
                <p class="text-secondary mb-0" id="modalMessage">Hemos recibido tu solicitud. Te contactaremos cuando confirmemos el pago.</p>
                <p class="text-secondary small mt-2">Recibirás un email con el número de seguimiento.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <a href="{{ route('producto.index') }}" class="btn btn-primary" id="seguirComprandoBtn">
                    <i class="fas fa-store me-2"></i>
                    Seguir comprando
                </a>
                <a href="{{ route('client.compras.index') }}" class="btn btn-outline-primary" id="verComprasBtn">
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
    border-color: #0a58ca;
}

.payment-option.selected {
    border-color: #0a58ca !important;
    background-color: #e7f1ff;
}

.payment-option .form-check-input:checked {
    background-color: #0a58ca;
    border-color: #0a58ca;
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

<script>
let metodoPagoSeleccionado = null;
let selectedElement = null;
let cuponValido = true;

function copiarTexto(texto) {
    // Copiar al portapapeles
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(() => {
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion('¡Copiado!', 'success');
            }
        }).catch(err => {
            console.error('Error al copiar:', err);
            alert('Presiona Ctrl+C para copiar: ' + texto);
        });
    } else {
        alert('Presiona Ctrl+C para copiar: ' + texto);
    }
}

function selectPaymentMethod(metodoSlug, element) {
    // Actualizar radio button
    const radio = document.getElementById(metodoSlug);
    if (radio) radio.checked = true;

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
    const infoSection = document.getElementById(metodoSlug + 'Info');
    if (infoSection) {
        infoSection.classList.remove('d-none');
    }

    metodoPagoSeleccionado = metodoSlug;
}

function enviarSolicitudPago() {
    if (!cuponValido) {
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('El cupón aplicado no es válido para esta moneda. Por favor, cambia de moneda o quita el cupón.', 'warning');
        } else {
            alert('El cupón aplicado no es válido para esta moneda. Por favor, cambia de moneda o quita el cupón.');
        }
        return;
    }

    // Validar que se haya seleccionado un método de pago
    if (!metodoPagoSeleccionado) {
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Por favor selecciona un método de pago', 'warning');
        } else {
            alert('Por favor selecciona un método de pago');
        }
        return;
    }

    // Validar términos y condiciones
    const terminos = document.getElementById('terminos');
    if (!terminos || !terminos.checked) {
        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Debes aceptar los términos y condiciones', 'warning');
        } else {
            alert('Debes aceptar los términos y condiciones');
        }
        return;
    }

    // Crear FormData para enviar archivos
    const formData = new FormData();

    // Agregar el token CSRF
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    // Agregar el método de pago
    formData.append('metodo_pago', metodoPagoSeleccionado);

    // Agregar términos
    formData.append('terminos', '1');

    // Agregar comentarios si existen
    const comentarios = document.querySelector('textarea[name="comentarios"]');
    if (comentarios && comentarios.value) {
        formData.append('comentarios', comentarios.value);
    }

    // Buscar la sección del método seleccionado y agregar todos sus campos
    const infoSection = document.getElementById(metodoPagoSeleccionado + 'Info');
    if (infoSection) {
        const inputs = infoSection.querySelectorAll('input, textarea');

        for (const input of inputs) {
            if (input.type === 'file') {
                if (input.files && input.files.length > 0) {
                    formData.append(input.name, input.files[0]);
                } else if (input.hasAttribute('required')) {
                    const label = input.closest('.mb-3')?.querySelector('.form-label')?.innerText || 'Archivo';
                    if (typeof window.mostrarNotificacion === 'function') {
                        window.mostrarNotificacion(`${label} es obligatorio`, 'warning');
                    } else {
                        alert(`${label} es obligatorio`);
                    }
                    return;
                }
            } else {
                if (input.value) {
                    formData.append(input.name, input.value);
                } else if (input.hasAttribute('required')) {
                    const label = input.closest('.mb-3')?.querySelector('.form-label')?.innerText || input.name;
                    if (typeof window.mostrarNotificacion === 'function') {
                        window.mostrarNotificacion(`${label} es obligatorio`, 'warning');
                    } else {
                        alert(`${label} es obligatorio`);
                    }
                    return;
                }
            }
        }
    }

    // Deshabilitar botón y mostrar loading
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loader me-2"></span>Procesando...';

    fetch('{{ route("checkout.procesar") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
            const modalMessage = document.getElementById('modalMessage');
            if (modalMessage) {
                modalMessage.textContent = data.message;
            }
            modal.show();
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion(data.message || 'Error al procesar la solicitud', 'danger');
            } else {
                alert(data.message || 'Error al procesar la solicitud');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (typeof window.mostrarNotificacion === 'function') {
            window.mostrarNotificacion('Error al procesar la solicitud', 'danger');
        } else {
            alert('Error al procesar la solicitud');
        }
    });
}

function aplicarCupon() {
    const codigo = document.getElementById('cuponInput').value.trim();

    if (!codigo) {
        mostrarMensajeCupon('Por favor ingresa un código de cupón', 'error');
        return;
    }

    const btn = document.getElementById('aplicarCuponBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loader me-2"></span>Aplicando...';

    fetch('{{ route("checkout.validar-cupon") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ codigo: codigo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            mostrarMensajeCupon(data.message || 'Cupón no válido', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensajeCupon('Error al aplicar el cupón', 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function quitarCupon() {
    if (!confirm('¿Quitar el cupón aplicado?')) return;

    const btn = document.getElementById('quitarCuponBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loader me-2"></span>Quitando...';

    fetch('{{ route("checkout.quitar-cupon") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al quitar el cupón');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al quitar el cupón');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function mostrarMensajeCupon(mensaje, tipo) {
    const infoDiv = document.getElementById('cuponInfo');
    const errorDiv = document.getElementById('cuponError');

    if (tipo === 'error') {
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${mensaje}`;
        errorDiv.style.display = 'block';
        infoDiv.style.display = 'none';
    } else {
        infoDiv.innerHTML = `<i class="fas fa-info-circle me-2"></i>${mensaje}`;
        infoDiv.style.display = 'block';
        errorDiv.style.display = 'none';
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
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
