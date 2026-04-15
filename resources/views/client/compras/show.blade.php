@extends('layouts.cliente.app')

@section('title', 'Detalle de Compra')
@section('page-title', 'Detalle de Solicitud de Pago')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('client.compras.index') }}" class="text-primary">Mis Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle #{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información de Productos -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary border-0 text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>
                        Productos Solicitados
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unitario</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $moneda = $solicitud->moneda;
                                    $simboloMoneda = $moneda ? $moneda->simbolo : 'S/';
                                    $usarPrecioLocal = $moneda && $moneda->codigo_iso === 'PEN';
                                    $estadoActual = $solicitud->estados->first();
                                @endphp

                                @forelse($solicitud->carrito->productos as $item)
                                    @php
                                        $producto = $item->producto;
                                        if ($usarPrecioLocal) {
                                            $precioUnitario = $item->precio_adquirido_local;
                                        } else {
                                            $precioUnitario = $item->precio_adquirido_usd;
                                        }
                                        $subtotal = $precioUnitario * $item->cantidad;
                                    @endphp
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                @php
                                                    $imagenUrl = null;
                                                    for($i = 1; $i <= 5; $i++) {
                                                        $campo = "imagen_url_{$i}";
                                                        if($producto->$campo) {
                                                            $imagenUrl = $producto->getImageUrl($campo);
                                                            if($imagenUrl) break;
                                                        }
                                                    }
                                                @endphp

                                                @if($imagenUrl)
                                                    <img src="{{ $imagenUrl }}"
                                                         alt="{{ $producto->nombre }}"
                                                         class="rounded me-3"
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-box fa-2x text-secondary"></i>
                                                    </div>
                                                @endif

                                                <div>
                                                    <strong>{{ $producto->nombre }}</strong>
                                                    <br>
                                                    <small class="text-secondary">{{ Str::limit($producto->descripcion ?? 'Sin descripción', 60) }}</small>
                                                    @if($item->aplica_descuento && $item->porcentaje_descuento > 0)
                                                        <br>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-tag me-1"></i>
                                                            -{{ number_format($item->porcentaje_descuento, 0) }}% OFF
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($producto->esDigital())
                                                <span class="badge bg-info">
                                                    <i class="fas fa-download me-1"></i> Digital
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-box me-1"></i> Físico
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-info">{{ $item->cantidad }}</span>
                                        </td>
                                        <td class="text-end align-middle">
                                            {{ $simboloMoneda }} {{ number_format($precioUnitario, 2) }}
                                            @if($item->aplica_descuento && $item->porcentaje_descuento > 0)
                                                <br>
                                                <small class="text-decoration-line-through text-secondary">
                                                    {{ $simboloMoneda }} {{ number_format($usarPrecioLocal ? $item->getPrecioOriginalLocalAttribute() : $item->getPrecioOriginalUsdAttribute(), 2) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-end align-middle">
                                            <strong>{{ $simboloMoneda }} {{ number_format($subtotal, 2) }}</strong>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($producto->esDigital() && $producto->url_recurso)
                                                @if($estadoActual && $estadoActual->estado === 'aprobado')
                                                    <a href="{{ route('client.comprobante.drive', [
                                                        'id_compras' => $solicitud->id,
                                                        'id_producto' => $producto->id
                                                    ]) }}"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fab fa-google-drive me-1"></i>
                                                        Ver contenido
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-lock me-1"></i>
                                                        {{ $estadoActual && $estadoActual->estado === 'rechazado' ? 'Solicitud Rechazada' : 'Pendiente de aprobación' }}
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-box-open fa-3x text-secondary mb-2"></i>
                                            <p class="mb-0">No hay productos en esta solicitud</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold">
                                        {{ $simboloMoneda }} {{ number_format($usarPrecioLocal ? $solicitud->carrito->total_local : $solicitud->carrito->total_usd, 2) }}
                                    </td>
                                </tr>
                                @if(($solicitud->descuento ?? 0) > 0)
                                <tr>
                                    <td colspan="5" class="text-end text-primary">Descuento aplicado:</td>
                                    <td class="text-end text-primary">
                                        -{{ $simboloMoneda }} {{ number_format($solicitud->descuento, 2) }}
                                    </td>
                                </tr>
                                @endif
                                @if(($solicitud->igv ?? 0) > 0)
                                <tr>
                                    <td colspan="5" class="text-end">IGV (18%):</td>
                                    <td class="text-end">
                                        {{ $simboloMoneda }} {{ number_format($solicitud->igv, 2) }}
                                    </td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td colspan="5" class="text-end fw-bold fs-5">Total:</td>
                                    <td class="text-end fw-bold fs-5 text-primary">
                                        {{ $simboloMoneda }} {{ number_format($solicitud->monto, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Información de la Solicitud -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary border-0 text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Información de la Solicitud
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-6">N° Solicitud:</dt>
                        <dd class="col-6">#{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</dd>

                        <dt class="col-6">Fecha de Creación:</dt>
                        <dd class="col-6">{{ $solicitud->created_at->format('d/m/Y H:i:s') }}</dd>

                        <dt class="col-6">Método de Pago:</dt>
                        <dd class="col-6">
                            @if($solicitud->metodoPago)
                                <span class="badge bg-secondary">
                                    <i class="fas {{ $solicitud->metodoPago->icono_class ?? 'fa-credit-card' }} me-1"></i>
                                    {{ $solicitud->metodoPago->nombre }}
                                </span>
                            @else
                                <span class="badge bg-secondary">No especificado</span>
                            @endif
                        </dd>

                        <dt class="col-6">Moneda:</dt>
                        <dd class="col-6">
                            @if($solicitud->moneda)
                                <span class="badge bg-info">
                                    <i class="fas fa-coins me-1"></i>
                                    {{ $solicitud->moneda->codigo_iso }} ({{ $solicitud->moneda->nombre }})
                                </span>
                            @else
                                <span class="badge bg-info">PEN (Sol Peruano)</span>
                            @endif
                        </dd>

                        @if($solicitud->cupon)
                        <dt class="col-6">Cupón Aplicado:</dt>
                        <dd class="col-6">
                            <span class="badge bg-primary">
                                <i class="fas fa-ticket-alt me-1"></i>
                                {{ $solicitud->cupon->codigo }}
                            </span>
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Estado de la Solicitud (Solo 3 estados) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary border-0 text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Estado de la Solicitud
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $estadosMap = [
                            'solicitado' => ['icon' => 'fa-paper-plane', 'color' => 'warning', 'text' => 'Solicitud Enviada', 'desc' => 'Tu solicitud ha sido enviada y está siendo evaluada'],
                            'aprobado' => ['icon' => 'fa-check-circle', 'color' => 'success', 'text' => 'Solicitud Aprobada', 'desc' => 'Tu solicitud ha sido aprobada. ¡Pronto recibirás tu contenido!'],
                            'rechazado' => ['icon' => 'fa-times-circle', 'color' => 'danger', 'text' => 'Solicitud Rechazada', 'desc' => 'Lo sentimos, tu solicitud ha sido rechazada']
                        ];
                        $estadoInfo = $estadosMap[$estadoActual->estado ?? 'solicitado'] ?? $estadosMap['solicitado'];
                    @endphp

                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas {{ $estadoInfo['icon'] }} fa-4x text-{{ $estadoInfo['color'] }}"></i>
                        </div>
                        <h6 class="fw-bold text-{{ $estadoInfo['color'] }}">
                            {{ $estadoInfo['text'] }}
                        </h6>
                        <p class="small text-secondary mb-0">
                            {{ $estadoInfo['desc'] }}
                        </p>
                    </div>

                    <!-- Timeline de estados -->
                    <div class="timeline">
                        <div class="d-flex mb-3 {{ $estadoActual->estado == 'solicitado' ? 'opacity-100' : 'opacity-50' }}">
                            <div class="me-3">
                                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-paper-plane text-white fa-sm"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">Solicitud Enviada</div>
                                <div class="small text-secondary">
                                    {{ $solicitud->created_at->format('d/m/Y H:i:s') }}
                                    <br>
                                    {{ $solicitud->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex mb-3 {{ $estadoActual->estado == 'aprobado' ? 'opacity-100' : ($estadoActual->estado == 'rechazado' ? 'opacity-100' : 'opacity-50') }}">
                            <div class="me-3">
                                <div class="rounded-circle {{ $estadoActual->estado == 'aprobado' ? 'bg-success' : ($estadoActual->estado == 'rechazado' ? 'bg-danger' : 'bg-secondary') }} d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas {{ $estadoActual->estado == 'aprobado' ? 'fa-check' : ($estadoActual->estado == 'rechazado' ? 'fa-times' : 'fa-clock') }} text-white fa-sm"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $estadoActual->estado == 'aprobado' ? 'Solicitud Aprobada' : ($estadoActual->estado == 'rechazado' ? 'Solicitud Rechazada' : 'Procesando Solicitud') }}
                                </div>
                                @if($estadoActual && $estadoActual->estado != 'solicitado')
                                    <div class="small text-secondary">
                                        {{ $estadoActual->created_at->format('d/m/Y H:i:s') }}
                                        <br>
                                        {{ $estadoActual->created_at->diffForHumans() }}
                                    </div>
                                    @if($estadoActual->comentarios)
                                        <div class="alert alert-{{ $estadoActual->estado == 'aprobado' ? 'success' : 'danger' }} mt-2 mb-0 py-2 small">
                                            <i class="fas fa-comment me-1"></i>
                                            {{ $estadoActual->comentarios }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Pago -->
            @if($solicitud->info_pago && count($solicitud->info_pago) > 0)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary border-0 text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Información de Pago
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($solicitud->info_pago as $key => $value)
                        @if(!empty($value))
                            <div class="mb-3">
                                <strong>{{ ucfirst(str_replace(['_', '-'], ' ', $key)) }}:</strong>
                                <div class="mt-1">
                                    @if(in_array($key, ['qr_code', 'qr', 'qr_url', 'imagen_qr', 'qrcode']))
                                        @if(filter_var($value, FILTER_VALIDATE_URL))
                                            <img src="{{ $value }}" alt="Código QR" class="img-fluid border rounded p-2" style="max-width: 200px; cursor: pointer;" onclick="showImageModal(this.src)">
                                        @else
                                            <p class="text-break">{{ $value }}</p>
                                        @endif
                                    @elseif(in_array($key, ['monto', 'amount', 'total']))
                                        <p class="fw-bold text-primary">{{ $simboloMoneda }} {{ number_format(floatval($value), 2) }}</p>
                                    @elseif(in_array($key, ['fecha', 'date', 'fecha_pago']))
                                        <p>{{ date('d/m/Y H:i:s', strtotime($value)) }}</p>
                                    @else
                                        @if(is_array($value))
                                            @foreach($value as $subKey => $subValue)
                                                <p class="text-break mb-1">
                                                    <strong>{{ ucfirst($subKey) }}:</strong>
                                                    {{ is_array($subValue) ? json_encode($subValue) : $subValue }}
                                                </p>
                                            @endforeach
                                        @else
                                            <p class="text-break">{{ $value }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Imágenes de comprobante mejorado -->
            @if($solicitud->imagen_1 || $solicitud->imagen_2 || $solicitud->imagen_3)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary border-0 text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-image me-2"></i>
                        Comprobantes de Pago
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @for($i = 1; $i <= 3; $i++)
                            @php
                                $campo = "imagen_{$i}";
                                $ruta = $solicitud->$campo ?? null;
                            @endphp
                            @if($ruta)
                                @php
                                    $hash = basename($ruta);
                                    $imageUrl = route('client.comprobante.ver', $hash);
                                @endphp
                                <div class="col-12">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center p-3">
                                            <img src="{{ $imageUrl }}"
                                                 alt="Comprobante {{ $i }}"
                                                 class="img-fluid rounded border cursor-pointer"
                                                 style="max-height: 150px; width: auto; object-fit: contain; cursor: pointer;"
                                                 onclick="showImageModal('{{ $imageUrl }}')">
                                        </div>
                                        <div class="card-footer bg-white border-0 text-center pt-0">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary me-2"
                                                    onclick="showImageModal('{{ $imageUrl }}')">
                                                <i class="fas fa-eye me-1"></i> Ampliar
                                            </button>
                                            <a href="{{ route('client.comprobante.descargar', $hash) }}"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-download me-1"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('client.compras.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver a Mis Compras
            </a>
        </div>
    </div>
</div>

<!-- Modal para ver imágenes -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-image me-2"></i>
                    Comprobante de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="modalImage" src="" alt="Comprobante" class="img-fluid" style="max-height: 80vh; width: auto;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cerrar
                </button>
                <a id="downloadLink" href="#" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i>
                    Descargar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function showImageModal(imageUrl) {
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        const modalImage = document.getElementById('modalImage');
        const downloadLink = document.getElementById('downloadLink');

        modalImage.src = imageUrl;
        downloadLink.href = imageUrl;

        modal.show();
    }
</script>
@endsection
