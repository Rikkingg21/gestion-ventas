// public/js/carrito.js

// Función auxiliar para obtener el token CSRF
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) {
        console.error('CSRF token meta tag no encontrado');
        return null;
    }
    return meta.content;
}

// Función para formatear precio según la moneda
function formatearPrecio(monto, moneda) {
    if (!moneda) {
        return `$${parseFloat(monto || 0).toFixed(2)}`;
    }
    const simbolo = moneda.simbolo || '$';
    return `${simbolo}${parseFloat(monto || 0).toFixed(2)}`;
}

// Función para actualizar el contenido del carrito en la sidebar
window.actualizarSidebarCarrito = function() {
    const cartItems = document.getElementById('cartItems');
    if (!cartItems) return;

    const token = getCsrfToken();
    if (!token) {
        console.error('No se puede actualizar el carrito: CSRF token no disponible');
        return;
    }

    fetch('/carrito/ver', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartCount = document.getElementById('cartCount');
            const cartTotal = document.getElementById('cartTotal');
            const cartItemsCount = document.getElementById('cartItemsCount');
            const checkoutBtn = document.getElementById('checkoutBtn');
            const monedaInfo = document.getElementById('monedaInfo');

            // Obtener información de la moneda actual
            const monedaActual = data.totales.moneda_actual;

            // Mostrar información de moneda
            if (monedaInfo) {
                if (!data.totales.acepta_pagos && data.totales.moneda_a_cobrar) {
                    monedaInfo.innerHTML = `
                        <i class="fas fa-info-circle me-1"></i>
                        Mostrando en ${monedaActual.nombre} (${monedaActual.codigo})
                        <br>
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Se cobrará en ${data.totales.moneda_a_cobrar.nombre} (${data.totales.moneda_a_cobrar.codigo})
                        </small>
                    `;
                } else {
                    monedaInfo.innerHTML = `
                        <i class="fas fa-info-circle me-1"></i>
                        Mostrando en ${monedaActual.nombre}
                    `;
                }
            }

            // Actualizar contadores
            if (cartCount) cartCount.textContent = data.totales.total_items;
            if (cartItemsCount) cartItemsCount.textContent = data.totales.total_items;

            // Formatear y mostrar el total actual (ya está en la moneda correcta)
            const totalFormateado = formatearPrecio(data.totales.total_actual, monedaActual);
            if (cartTotal) cartTotal.textContent = totalFormateado;

            // Actualizar botón de checkout
            if (checkoutBtn) {
                if (data.totales.total_items > 0) {
                    checkoutBtn.classList.remove('disabled');
                } else {
                    checkoutBtn.classList.add('disabled');
                }
            }

            // Actualizar lista de items
            if (data.items && data.items.length > 0) {
                let html = '';
                data.items.forEach(item => {
                    const tieneDescuento = item.aplica_descuento && item.porcentaje_descuento > 0;
                    const esDigital = item.tipo_producto === 'digital';

                    // Usar el precio_mostrar que ya viene calculado del backend
                    const precioFormateado = formatearPrecio(item.precio_mostrar, monedaActual);

                    html += `
                        <div class="cart-item p-3 border-bottom" data-item-id="${item.id}">
                            <div class="d-flex">
                                <!-- Imagen del producto -->
                                <div class="flex-shrink-0">
                                    ${item.imagen ?
                                        `<img src="${item.imagen}" alt="${escapeHtml(item.nombre)}"
                                            class="rounded" style="width: 50px; height: 50px; object-fit: cover;">` :
                                        `<div class="bg-light rounded d-flex align-items-center justify-content-center"
                                            style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-secondary"></i>
                                        </div>`
                                    }
                                </div>

                                <!-- Información del producto -->
                                <div class="flex-grow-1 ms-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 small fw-bold" style="font-size: 0.8rem;">
                                                ${escapeHtml(item.nombre.substring(0, 30))}${item.nombre.length > 30 ? '...' : ''}
                                            </h6>
                                        </div>
                                        <button class="btn btn-link text-danger p-0 ms-1"
                                                onclick="eliminarItemCarrito(${item.id})"
                                                title="Eliminar">
                                            <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </div>

                                    <!-- Precio y cantidad -->
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        ${esDigital ? `
                                            <div class="d-flex align-items-center">
                                                <small class="text-info">
                                                    <i class="fas fa-info-circle me-1"></i>Producto digital
                                                </small>
                                            </div>
                                        ` : `
                                            <div class="d-flex align-items-center">
                                                <button class="btn btn-sm btn-outline-secondary px-1 py-0"
                                                        onclick="actualizarCantidad(${item.id}, ${item.cantidad - 1})"
                                                        ${item.cantidad <= 1 ? 'disabled' : ''}>
                                                    <i class="fas fa-minus" style="font-size: 0.7rem;"></i>
                                                </button>
                                                <span class="mx-1 small">${item.cantidad}</span>
                                                <button class="btn btn-sm btn-outline-secondary px-1 py-0"
                                                        onclick="actualizarCantidad(${item.id}, ${item.cantidad + 1})"
                                                        ${item.stock_disponible && item.cantidad >= item.stock_disponible ? 'disabled' : ''}>
                                                    <i class="fas fa-plus" style="font-size: 0.7rem;"></i>
                                                </button>
                                            </div>
                                        `}
                                        <span class="small fw-bold text-primary">
                                            ${precioFormateado}
                                        </span>
                                    </div>

                                    ${tieneDescuento ? `
                                        <small class="text-warning d-block mt-1" style="font-size: 0.65rem;">
                                            <i class="fas fa-tag me-1"></i>
                                            -${item.porcentaje_descuento}% descuento
                                        </small>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });

                // Agregar botón "Ver más" si hay muchos items
                if (data.items.length > 3) {
                    html += `
                        <div class="text-center p-2">
                            <a href="/carrito" class="btn btn-outline-success btn-sm">
                                Ver todos (${data.items.length})
                            </a>
                        </div>
                    `;
                }

                cartItems.innerHTML = html;
            } else {
                cartItems.innerHTML = `
                    <div class="text-center text-secondary py-5">
                        <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">Tu carrito está vacío</p>
                        <small class="text-muted">¡Agrega productos para comenzar!</small>
                    </div>
                `;
            }
        } else if (data.error) {
            console.error('Error del servidor:', data.error);
            cartItems.innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p class="mb-0">${escapeHtml(data.error)}</p>
                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="actualizarSidebarCarrito()">
                        Reintentar
                    </button>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error al cargar carrito:', error);
        if (cartItems) {
            cartItems.innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p class="mb-0">Error al cargar el carrito</p>
                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="actualizarSidebarCarrito()">
                        Reintentar
                    </button>
                </div>
            `;
        }
    });
};

// Función auxiliar para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Función para actualizar cantidad (solo para productos físicos)
window.actualizarCantidad = function(itemId, nuevaCantidad) {
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
            actualizarSidebarCarrito();
            mostrarNotificacion('Cantidad actualizada', 'success');
        } else {
            mostrarNotificacion(data.error || 'Error al actualizar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al actualizar cantidad', 'danger');
    });
};

// Función para eliminar item
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
            actualizarSidebarCarrito();
            mostrarNotificacion('Producto eliminado', 'success');
        } else {
            mostrarNotificacion(data.error || 'Error al eliminar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al eliminar producto', 'danger');
    });
};

// Función para mostrar notificaciones
window.mostrarNotificacion = function(mensaje, tipo = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.maxWidth = '300px';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            <small>${escapeHtml(mensaje)}</small>
        </div>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
};

// Función para cambiar moneda
window.cambiarMoneda = function(currencyCode) {
    const token = getCsrfToken();
    if (!token) {
        mostrarNotificacion('Error de seguridad: Token CSRF no disponible', 'danger');
        return;
    }

    fetch('/cambiar-moneda', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ currency: currencyCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar el sidebar del carrito
            if (typeof window.actualizarSidebarCarrito === 'function') {
                window.actualizarSidebarCarrito();
            }

            // Recargar la página para actualizar todos los precios
            setTimeout(() => {
                location.reload();
            }, 500);

            mostrarNotificacion(`Moneda cambiada a ${currencyCode}`, 'success');
        } else {
            mostrarNotificacion(data.message || 'Error al cambiar la moneda', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al cambiar la moneda', 'danger');
    });
};

// Inicializar carrito al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.actualizarSidebarCarrito === 'function') {
        // Pequeño retraso para asegurar que todo esté cargado
        setTimeout(() => {
            window.actualizarSidebarCarrito();
        }, 100);
    }
});
