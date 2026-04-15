{{-- resources/views/client/partials/carrito-sidebar.blade.php --}}
@php
    $totalItems = $carritoData['totalItems'] ?? 0;
    $moneda_actual = $carritoData['moneda_actual'] ?? (object)['simbolo' => '$', 'nombre' => 'USD', 'codigo_iso' => 'USD', 'id' => null];
    $acepta_pagos = $carritoData['acepta_pagos'] ?? false;
@endphp

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Mi Carrito
            <span class="badge bg-light text-primary float-end" id="cartCount">{{ $totalItems }}</span>
        </h5>
    </div>
    <div class="card-body p-0" id="cartContainer">
        <!-- Lista de items del carrito -->
        <div id="cartItems" class="cart-items-list" style="max-height: 350px; overflow-y: auto;">
            @if($totalItems > 0)
                <div class="text-center text-secondary py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 mb-0">Cargando carrito...</p>
                </div>
            @else
                <div class="text-center text-secondary py-5">
                    <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">Tu carrito está vacío</p>
                    <small class="text-muted">¡Agrega productos para comenzar!</small>
                </div>
            @endif
        </div>

        <!-- Resumen del carrito -->
        <div class="cart-summary p-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary">Total:</span>
                <span class="fw-bold text-primary fs-5" id="cartTotal">
                    {{ $moneda_actual->simbolo }}0.00
                </span>
            </div>

            <a href="{{ route('carrito.ver') }}"
               class="btn btn-primary w-100 {{ $totalItems > 0 ? '' : 'disabled' }}"
               id="checkoutBtn">
                <i class="fas fa-arrow-right me-2"></i>
                Proceder al pago
            </a>

            @if($totalItems > 0)
                <small class="text-muted d-block text-center mt-2" id="monedaInfo">
                    <i class="fas fa-info-circle me-1"></i>
                    Mostrando en {{ $moneda_actual->nombre }}
                    @if(!$acepta_pagos)
                        <br>
                        <span class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Esta moneda no acepta pagos
                        </span>
                    @endif
                </small>
            @endif
        </div>
    </div>
</div>
