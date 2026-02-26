<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Mi Carrito
            <span class="badge bg-light text-success float-end" id="cartCount">{{ $totalItems ?? 0 }}</span>
        </h5>
    </div>
    <div class="card-body p-0" id="cartContainer">
        <!-- Lista de items del carrito -->
        <div id="cartItems" class="cart-items-list" style="max-height: 350px; overflow-y: auto;">
            @if(($totalItems ?? 0) > 0)
                <div class="text-center text-secondary py-4">
                    <div class="spinner-border text-success" role="status">
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
                <span class="text-secondary">Subtotal:</span>
                <span class="fw-bold" id="cartSubtotal">$0.00</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary">Total items:</span>
                <span class="fw-bold" id="cartItemsCount">{{ $totalItems ?? 0 }}</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center fw-bold fs-5 mb-3">
                <span>Total:</span>
                <span class="text-success" id="cartTotal">$0.00</span>
            </div>

            <a href="{{ route('carrito.ver') }}"
               class="btn btn-success w-100 {{ ($totalItems ?? 0) > 0 ? '' : 'disabled' }}"
               id="checkoutBtn">
                <i class="fas fa-arrow-right me-2"></i>
                Proceder al pago
            </a>

            @if(($totalItems ?? 0) > 0)
                <small class="text-muted d-block text-center mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Envío calculado en el siguiente paso
                </small>
            @endif
        </div>
    </div>
</div>
