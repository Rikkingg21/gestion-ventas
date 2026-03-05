@extends('layouts.admin.app')

@section('title', 'Detalle de Solicitud')
@section('page-title', 'Detalle de Solicitud #' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna izquierda - Timeline y Productos -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Timeline de estados -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-history mr-2 text-emerald-600"></i>
                        Historial de Estados
                    </h5>
                </div>
                <div class="p-6">
                    @if($solicitud->estados->isEmpty())
                        <p class="text-gray-500 text-center py-4">No hay registros de estados</p>
                    @else
                        <div class="relative">
                            @foreach($solicitud->estados as $index => $estado)
                                @php
                                    $claseEstado = match($estado->estado) {
                                        'aprobado' => 'bg-emerald-500',
                                        'rechazado' => 'bg-rose-500',
                                        default => 'bg-amber-500'
                                    };
                                    $iconoEstado = match($estado->estado) {
                                        'aprobado' => 'fa-check-circle',
                                        'rechazado' => 'fa-times-circle',
                                        default => 'fa-clock'
                                    };
                                @endphp

                                <div class="flex mb-6 {{ !$loop->last ? 'pb-2' : '' }}">
                                    <!-- Icono -->
                                    <div class="flex-shrink-0 mr-4">
                                        <div class="{{ $claseEstado }} text-white rounded-full w-10 h-10 flex items-center justify-center">
                                            <i class="fas {{ $iconoEstado }}"></i>
                                        </div>
                                    </div>

                                    <!-- Contenido -->
                                    <div class="flex-grow">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h6 class="text-base font-semibold text-gray-900">
                                                {{ ucfirst($estado->estado) }}
                                            </h6>
                                            <span class="text-sm text-gray-500">
                                                {{ $estado->created_at->format('d/m/Y H:i:s') }}
                                            </span>
                                        </div>
                                        @if($estado->comentarios)
                                            <div class="mt-2 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                                                <i class="fas fa-quote-left mr-2 text-gray-400"></i>
                                                {{ $estado->comentarios }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if(!$loop->last)
                                    <div class="ml-5 pl-5 pb-2">
                                        <div class="w-0.5 h-8 bg-gray-200"></div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Productos -->
            @if($solicitud->carrito && $solicitud->carrito->productos->isNotEmpty())
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-box mr-2 text-emerald-600"></i>
                            Productos
                        </h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock Actual</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($solicitud->carrito->productos as $item)
                                    @php
                                        $producto = $item->producto;
                                        $stockActual = $producto->esFisico() && $producto->stock ? $producto->stock->cantidad : null;
                                        $claseStock = $stockActual !== null
                                            ? ($stockActual > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800')
                                            : 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                                            @if($producto->sku)
                                                <div class="text-xs text-gray-500">SKU: {{ $producto->sku }}</div>
                                            @endif
                                            @if($item->aplica_descuento && $solicitud->cupon)
                                                <div class="text-xs text-emerald-600 mt-1">
                                                    <i class="fas fa-tag mr-1"></i>
                                                    Descuento por cupón aplicado
                                                </div>
                                            @elseif($item->aplica_descuento)
                                                <div class="text-xs text-amber-600 mt-1">
                                                    <i class="fas fa-percent mr-1"></i>
                                                    Descuento del {{ $item->porcentaje_descuento }}%
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $producto->esDigital() ? 'bg-sky-100 text-sky-800' : 'bg-indigo-100 text-indigo-800' }}">
                                                <i class="fas fa-{{ $producto->esDigital() ? 'cloud' : 'box' }} mr-1"></i>
                                                {{ ucfirst($producto->tipo_producto) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-medium text-gray-900">{{ $item->cantidad }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="text-sm text-gray-900">
                                                {{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($item->precio_adquirido_local, 2) }}
                                            </div>
                                            @if($item->precio_adquirido_usd != $item->precio_adquirido_local)
                                                <div class="text-xs text-gray-500">
                                                    ≈ ${{ number_format($item->precio_adquirido_usd, 2) }} USD
                                                </div>
                                            @endif
                                            @if($item->aplica_descuento)
                                                <div class="text-xs font-semibold text-emerald-600 mt-1">
                                                    -{{ $item->porcentaje_descuento }}%
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center font-medium text-emerald-600">
                                            <div class="text-base">
                                                {{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($item->subtotal_local, 2) }}
                                            </div>
                                            @if($item->subtotal_usd != $item->subtotal_local)
                                                <div class="text-xs text-gray-500">
                                                    ≈ ${{ number_format($item->subtotal_usd, 2) }} USD
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($producto->esFisico())
                                                @if($stockActual !== null)
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $claseStock }}">
                                                        <i class="fas fa-{{ $stockActual > 0 ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                                        {{ $stockActual }}
                                                    </span>
                                                    @if($stockActual < $item->cantidad)
                                                        <div class="text-xs text-rose-600 mt-1">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            Stock insuficiente
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-500">Sin stock</span>
                                                @endif
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <i class="fas fa-infinity mr-1"></i>
                                                    Ilimitado
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <!-- Totales adicionales -->
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-700">
                                        Subtotal:
                                    </td>
                                    <td class="px-6 py-3 text-center font-bold text-emerald-600">
                                        {{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($solicitud->carrito->total_local, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                                @if($solicitud->cupon)
                                <tr>
                                    <td colspan="4" class="px-6 py-2 text-right text-sm font-medium text-gray-700">
                                        Descuento por cupón:
                                    </td>
                                    <td class="px-6 py-2 text-center font-bold text-emerald-600">
                                        -{{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format(($solicitud->carrito->total_local - $solicitud->monto), 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                                @endif
                                <tr class="border-t-2 border-gray-300">
                                    <td colspan="4" class="px-6 py-3 text-right text-base font-bold text-gray-900">
                                        TOTAL:
                                    </td>
                                    <td class="px-6 py-3 text-center text-xl font-bold text-emerald-600">
                                        {{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($solicitud->monto, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna derecha - Acciones y Detalles -->
        <div class="space-y-6">
            <!-- Acciones -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-tasks mr-2 text-emerald-600"></i>
                        Acciones
                    </h5>
                </div>
                <div class="p-6">
                    @php
                        $ultimoEstado = $solicitud->estados->first();
                        $estadoActual = $ultimoEstado ? $ultimoEstado->estado : 'solicitado';
                        $puedeModificar = !in_array($estadoActual, ['aprobado', 'rechazado']);
                    @endphp

                    @if($puedeModificar)
                        <form id="formActualizarEstado" class="space-y-4">
                            @csrf
                            <input type="hidden" name="solicitud_id" value="{{ $solicitud->id }}">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <select name="estado" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="aprobado">Aprobar Solicitud</option>
                                    <option value="rechazado">Rechazar Solicitud</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Comentarios</label>
                                <textarea name="comentarios" rows="3"
                                          class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200"
                                          placeholder="Motivo de la aprobación o rechazo..."></textarea>
                                <p class="mt-1 text-xs text-gray-500">Opcional pero recomendado</p>
                            </div>

                            <button type="submit" id="btnActualizar"
                                    class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Actualizar Estado
                            </button>
                        </form>
                    @else
                        <div class="p-4 rounded-lg {{ $estadoActual == 'aprobado' ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">
                            <div class="flex items-center">
                                <i class="fas fa-{{ $estadoActual == 'aprobado' ? 'check-circle' : 'times-circle' }} mr-2 text-lg"></i>
                                <span class="font-medium">
                                    Esta solicitud ya fue {{ $estadoActual == 'aprobado' ? 'aprobada' : 'rechazada' }} y no puede modificarse.
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información de la solicitud -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-receipt mr-2 text-emerald-600"></i>
                        Detalles de la Solicitud
                    </h5>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">ID:</span>
                        <span class="font-medium">#{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Fecha:</span>
                        <span>{{ $solicitud->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Método:</span>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            <i class="fas fa-{{ $solicitud->metodo_pago == 'paypal' ? 'paypal' : 'mobile-alt' }} mr-1"></i>
                            {{ ucfirst($solicitud->metodo_pago) }}
                        </span>
                    </div>

                    <!-- NUEVO: Moneda -->
                    @if($solicitud->moneda)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Moneda:</span>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            {{ $solicitud->moneda->codigo_iso }} ({{ $solicitud->moneda->simbolo }})
                        </span>
                    </div>
                    @endif

                    <!-- NUEVO: Cupón aplicado -->
                    @if($solicitud->cupon)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cupón:</span>
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                            <i class="fas fa-tag mr-1"></i>
                            {{ $solicitud->cupon->codigo }}
                        </span>
                    </div>
                    @if($solicitud->cupon->porcentaje_descuento)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Descuento:</span>
                        <span class="text-emerald-600">{{ $solicitud->cupon->porcentaje_descuento }}%</span>
                    </div>
                    @elseif($solicitud->cupon->descuento_monto)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Descuento:</span>
                        <span class="text-emerald-600">{{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($solicitud->cupon->descuento_monto, 2) }}</span>
                    </div>
                    @endif
                    @endif

                    <hr class="border-gray-200">

                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span>{{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($solicitud->carrito->total_local ?? 0, 2) }}</span>
                    </div>

                    @if($solicitud->cupon)
                    <div class="flex justify-between text-emerald-600">
                        <span class="text-gray-600">Descuento aplicado:</span>
                        <span>-{{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format(($solicitud->carrito->total_local ?? 0) - $solicitud->monto, 2) }}</span>
                    </div>
                    @endif

                    <hr class="border-gray-200">

                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-800">Total:</span>
                        <span class="text-xl font-bold text-emerald-600">
                            {{ $solicitud->moneda->simbolo ?? 'S/' }} {{ number_format($solicitud->monto, 2) }}
                        </span>
                    </div>

                    @if($solicitud->imagen_1)
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante:</label>
                            <a href="{{ route('admin.solicitudes-pedidos.comprobante', $solicitud->id) }}"
                            target="_blank"
                            class="inline-flex items-center justify-center w-full px-4 py-2 border border-sky-300 text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg transition duration-200">
                                <i class="fas fa-image mr-2"></i>
                                Ver comprobante
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Datos del cliente -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-user mr-2 text-emerald-600"></i>
                        Datos del Cliente
                    </h5>
                </div>
                <div class="p-6 space-y-2">
                    <p class="font-medium text-gray-900">{{ $solicitud->cliente->nombre }} {{ $solicitud->cliente->apellidos }}</p>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-id-card mr-2 text-gray-400 w-4"></i>
                        {{ $solicitud->cliente->tipo_documento }}: {{ $solicitud->cliente->nro_documento }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-envelope mr-2 text-gray-400 w-4"></i>
                        {{ $solicitud->cliente->email }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-phone mr-2 text-gray-400 w-4"></i>
                        {{ $solicitud->cliente->telefono }}
                    </p>
                    @if($solicitud->cliente->direccion)
                        <p class="text-sm text-gray-600">
                            <i class="fas fa-map-marker-alt mr-2 text-gray-400 w-4"></i>
                            {{ $solicitud->cliente->direccion }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación (con Tailwind) -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-emerald-600 px-6 py-4">
                <h5 class="text-lg font-semibold text-white">
                    <i class="fas fa-check-circle mr-2"></i>
                    ¡Estado Actualizado!
                </h5>
            </div>
            <div class="bg-white px-6 py-8 text-center">
                <i class="fas fa-check-circle text-emerald-600 text-5xl mb-4"></i>
                <h6 class="text-lg font-medium text-gray-900 mb-2">Estado actualizado correctamente</h6>
                <p class="text-gray-500" id="modalMessage"></p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-center">
                <button type="button" onclick="cerrarModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition duration-200">
                    <i class="fas fa-check mr-2"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarModal(mensaje) {
    document.getElementById('modalMessage').textContent = mensaje;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('formActualizarEstado')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('btnActualizar');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...';

    const formData = new FormData(this);

    fetch('{{ route("admin.solicitudes-pedidos.update-estado", $solicitud->id) }}', {
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
            mostrarModal(data.message);
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert(data.message || 'Error al actualizar el estado');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});
</script>
@endsection
