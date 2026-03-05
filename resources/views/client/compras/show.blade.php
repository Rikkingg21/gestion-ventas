@extends('layouts.cliente.app')

@section('title', 'Detalle de Solicitud')
@section('page-title', 'Detalle de Solicitud')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('client.compras.index') }}" class="text-success">Mis Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Solicitud #{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Información principal -->
        <div class="col-lg-8">
            <!-- Timeline de estados -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historial de Estados
                    </h5>
                </div>
                <div class="card-body">
                    @if($solicitud->estados->isEmpty())
                        <p class="text-secondary mb-0">No hay registros de estados</p>
                    @else
                        <div class="timeline">
                            @foreach($solicitud->estados as $estado)
                                @php
                                    $claseEstado = match($estado->estado) {
                                        'Aprobado' => 'success',
                                        'Rechazado' => 'danger',
                                        default => 'warning'
                                    };
                                    $iconoEstado = match($estado->estado) {
                                        'Aprobado' => 'fa-check-circle',
                                        'Rechazado' => 'fa-times-circle',
                                        default => 'fa-clock'
                                    };
                                @endphp
                                <div class="d-flex mb-3">
                                    <div class="me-3">
                                        <div class="bg-{{ $claseEstado }} text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas {{ $iconoEstado }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1 text-{{ $claseEstado }}">
                                                {{ ucfirst($estado->estado) }}
                                            </h6>
                                            <small class="text-secondary">
                                                {{ $estado->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                        @if($estado->comentarios)
                                            <p class="mt-1 mb-0 text-secondary bg-light p-2 rounded">
                                                <i class="fas fa-quote-left me-1"></i>
                                                {{ $estado->comentarios }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <div class="ms-4 ps-2 pb-2">
                                        <div style="width: 2px; height: 20px; background-color: #e9ecef;"></div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Productos del carrito -->
            @if($solicitud->carrito && $solicitud->carrito->productos->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-box me-2"></i>
                            Productos
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
                                    @foreach($solicitud->carrito->productos as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <span class="fw-bold">{{ $item->producto->nombre }}</span>
                                                        @if($item->producto->sku)
                                                            <br>
                                                            <small class="text-secondary">SKU: {{ $item->producto->sku }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $item->producto->esDigital() ? 'bg-info' : 'bg-primary' }}">
                                                    {{ ucfirst($item->producto->tipo_producto) }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $item->cantidad }}</td>
                                            <td class="text-center">
                                                S/ {{ number_format($item->precio_adquirido_local, 2) }}
                                                @if($item->aplica_descuento)
                                                    <br>
                                                    <small class="text-success">-{{ $item->porcentaje_descuento }}%</small>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold text-success">
                                                S/ {{ number_format($item->subtotal_local, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Resumen y detalles -->
        <div class="col-lg-4">
            <!-- Información de la solicitud -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Detalles de la Solicitud
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">N° Solicitud:</span>
                        <span class="fw-bold">#{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Fecha:</span>
                        <span>{{ $solicitud->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Método de pago:</span>
                        <span class="badge bg-secondary">{{ ucfirst($solicitud->metodo_pago) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal:</span>
                        <span>S/ {{ number_format($solicitud->carrito->total_local ?? 0, 2) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-success">S/ {{ number_format($solicitud->monto, 2) }}</span>
                    </div>

                    @if($solicitud->imagen_1)
                        <div class="mt-3">
                            <label class="form-label text-secondary fw-bold">Comprobante de pago:</label>
                            <a href="{{ asset('storage/' . $solicitud->imagen_1) }}" target="_blank" class="btn btn-outline-success w-100">
                                <i class="fas fa-image me-2"></i>
                                Ver comprobante
                            </a>
                        </div>
                    @endif

                    <a href="{{ route('client.compras.index') }}" class="btn btn-outline-success w-100 mt-3">
                        <i class="fas fa-arrow-left me-2"></i>
                        Volver a mis compras
                    </a>
                </div>
            </div>

            <!-- Información del cliente -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Datos del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 fw-bold">{{ $cliente->nombre }} {{ $cliente->apellidos }}</p>
                    <p class="mb-1 text-secondary">{{ $cliente->email }}</p>
                    <p class="mb-0 text-secondary">{{ $cliente->telefono }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 10px;
}
</style>
@endsection
