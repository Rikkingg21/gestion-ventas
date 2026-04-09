@extends('layouts.cliente.app')

@section('title', 'Mis Compras')
@section('page-title', 'Mis Compras')

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mis Compras</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>
                        Historial de Solicitudes de Pago
                    </h5>
                </div>
                <div class="card-body">
                    @if($solicitudes->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-secondary mb-3"></i>
                            <h5>No tienes solicitudes de pago registradas</h5>
                            <p class="text-secondary">¡Explora nuestros productos y realiza tu primera compra!</p>
                            <a href="{{ route('producto.index') }}" class="btn btn-success">
                                <i class="fas fa-store me-2"></i>
                                Ir a la tienda
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>N° Solicitud</th>
                                        <th>Fecha</th>
                                        <th>Método de Pago</th>
                                        <th>Moneda</th>
                                        <th>Monto</th>
                                        <th>Productos</th>
                                        <th>Estado Actual</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($solicitudes as $solicitud)
                                        @php
                                            $ultimoEstado = $solicitud->estados->first();
                                            $estadoActual = $ultimoEstado ? $ultimoEstado->estado : 'solicitado';

                                            $claseEstado = match($estadoActual) {
                                                'aprobado' => 'success',
                                                'rechazado' => 'danger',
                                                default => 'warning'
                                            };

                                            $iconoEstado = match($estadoActual) {
                                                'aprobado' => 'fa-check-circle',
                                                'rechazado' => 'fa-times-circle',
                                                default => 'fa-clock'
                                            };

                                            $totalProductos = $solicitud->carrito ? $solicitud->carrito->productos->sum('cantidad') : 0;
                                            $metodoPago = $solicitud->metodoPago;
                                            $moneda = $solicitud->moneda;
                                            $simboloMoneda = $moneda ? $moneda->simbolo : 'S/';
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold">#{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td>
                                                {{ $solicitud->created_at->format('d/m/Y H:i') }}
                                                <br>
                                                <small class="text-secondary">{{ $solicitud->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($metodoPago)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas {{ $metodoPago->icono_class ?? 'fa-credit-card' }} me-1"></i>
                                                        {{ $metodoPago->nombre }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No especificado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($moneda)
                                                    <span class="badge bg-info">
                                                        {{ $moneda->codigo_iso }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-info">PEN</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    {{ $simboloMoneda }} {{ number_format($solicitud->monto, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    {{ $totalProductos }} productos
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $claseEstado }} text-white">
                                                    <i class="fas {{ $iconoEstado }} me-1"></i>
                                                    {{ ucfirst($estadoActual) }}
                                                </span>
                                                @if($ultimoEstado && $ultimoEstado->comentarios)
                                                    <br>
                                                    <small class="text-secondary" data-bs-toggle="tooltip" title="{{ $ultimoEstado->comentarios }}">
                                                        <i class="fas fa-comment me-1"></i>
                                                        Ver comentario
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('client.compras.show', $solicitud->id) }}"
                                                   class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Ver detalles
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    })
</script>
@endsection
