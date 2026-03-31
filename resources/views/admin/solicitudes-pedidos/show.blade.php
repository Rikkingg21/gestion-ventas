@extends('layouts.admin.app')

@section('title', 'Detalle de Solicitud')
@section('page-title', 'Detalle de Solicitud #' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.solicitudes-pedidos.index') }}" class="text-emerald-600 hover:text-emerald-800">
            <i class="fas fa-arrow-left mr-2"></i>Volver a la lista
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna izquierda: Información del cliente y productos -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información del cliente -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-emerald-600">
                    <h5 class="text-white font-semibold">
                        <i class="fas fa-user mr-2"></i>Información del Cliente
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Nombre completo</label>
                            <p class="font-medium">{{ $solicitud->cliente->nombres }} {{ $solicitud->cliente->apellido_paterno }} {{ $solicitud->cliente->apellido_materno }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Email</label>
                            <p class="font-medium">{{ $solicitud->cliente->email }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Documento</label>
                            <p class="font-medium">{{ $solicitud->cliente->tipo_documento }}: {{ $solicitud->cliente->nro_documento }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Teléfono</label>
                            <p class="font-medium">{{ $solicitud->cliente->telefono }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos del pedido -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-emerald-600">
                    <h5 class="text-white font-semibold">
                        <i class="fas fa-boxes mr-2"></i>Productos del Pedido
                    </h5>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Producto</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500">Cantidad</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Precio Unit.</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($solicitud->carrito->productos as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $item->producto->nombre }}</div>
                                        <div class="text-xs text-gray-500">{{ ucfirst($item->producto->tipo_producto) }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">{{ $item->cantidad }}</td>
                                    <td class="px-6 py-4 text-right">
                                        {{ $solicitud->moneda->simbolo }} {{ number_format($item->precio_adquirido_local, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium">
                                        {{ $solicitud->moneda->simbolo }} {{ number_format($item->subtotal_local, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right font-medium">Subtotal:</td>
                                <td class="px-6 py-3 text-right font-medium">{{ $solicitud->moneda->simbolo }} {{ number_format($solicitud->carrito->total_local, 2) }}</td>
                            </tr>
                            @if(isset($solicitud->info_pago['cupon_aplicado']))
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right font-medium text-emerald-600">Descuento ({{ $solicitud->info_pago['cupon_aplicado']['codigo'] }}):</td>
                                <td class="px-6 py-3 text-right font-medium text-emerald-600">
                                    -{{ $solicitud->moneda->simbolo }} {{ number_format($solicitud->info_pago['cupon_aplicado']['descuento'], 2) }}
                                </td>
                            </tr>
                            @endif
                            <tr class="border-t-2 border-gray-200">
                                <td colspan="3" class="px-6 py-3 text-right font-bold text-lg">Total:</td>
                                <td class="px-6 py-3 text-right font-bold text-lg text-emerald-600">
                                    {{ $solicitud->moneda->simbolo }} {{ number_format($solicitud->monto, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Información del pago -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-emerald-600">
                    <h5 class="text-white font-semibold">
                        <i class="fas fa-credit-card mr-2"></i>Información del Pago
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Método de pago</label>
                            <p class="font-medium">
                                <i class="fas {{ $solicitud->metodoPago->icono_class ?? 'fa-credit-card' }} mr-1"></i>
                                {{ $solicitud->metodoPago->nombre }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Moneda</label>
                            <p class="font-medium">{{ $solicitud->moneda->codigo_iso }} ({{ $solicitud->moneda->simbolo }})</p>
                        </div>
                    </div>

                    @if(!empty($solicitud->info_pago))
                        <div class="border-t pt-4">
                            <label class="text-xs text-gray-500 uppercase mb-2 block">Datos del pago</label>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @foreach($solicitud->info_pago as $key => $value)
                                    @if($key != 'cupon_aplicado' && $key != 'comentarios_cliente')
                                        <div class="mb-2">
                                            <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="text-sm text-gray-600 ml-2">{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                                @if(isset($solicitud->info_pago['comentarios_cliente']))
                                    <div class="mt-3 pt-3 border-t">
                                        <span class="text-sm font-medium text-gray-700">Comentarios del cliente:</span>
                                        <p class="text-sm text-gray-600 mt-1">{{ $solicitud->info_pago['comentarios_cliente'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Columna derecha: Comprobantes y acciones -->
        <div class="space-y-6">
            <!-- Comprobantes -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-emerald-600">
                    <h5 class="text-white font-semibold">
                        <i class="fas fa-image mr-2"></i>Comprobantes
                    </h5>
                </div>
                <div class="p-6">
                    @php
                        $imagenes = $solicitud->getImagenes();
                    @endphp
                    @if(count($imagenes) > 0)
                        <div class="space-y-3">
                            @foreach($imagenes as $index => $imagenUrl)
                                <div>
                                    <a href="{{ $imagenUrl }}" target="_blank" class="block">
                                        <div class="border rounded-lg p-3 hover:bg-gray-50 transition">
                                            <i class="fas fa-file-image text-emerald-600 mr-2"></i>
                                            Comprobante {{ $index + 1 }}
                                            <i class="fas fa-external-link-alt text-xs ml-2 text-gray-400"></i>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No hay comprobantes adjuntos</p>
                    @endif
                </div>
            </div>

            <!-- Estado y acciones -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-emerald-600">
                    <h5 class="text-white font-semibold">
                        <i class="fas fa-chart-line mr-2"></i>Estado Actual
                    </h5>
                </div>
                <div class="p-6">
                    @php
                        $ultimoEstado = $solicitud->estados->first();
                        $estadoActual = $ultimoEstado ? $ultimoEstado->estado : 'solicitado';
                        $claseEstado = match($estadoActual) {
                            'aprobado' => 'bg-emerald-100 text-emerald-800',
                            'rechazado' => 'bg-rose-100 text-rose-800',
                            default => 'bg-amber-100 text-amber-800'
                        };
                    @endphp
                    <div class="text-center mb-4">
                        <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full {{ $claseEstado }}">
                            {{ ucfirst($estadoActual) }}
                        </span>
                        @if($ultimoEstado && $ultimoEstado->comentarios)
                            <p class="text-sm text-gray-600 mt-2">{{ $ultimoEstado->comentarios }}</p>
                        @endif
                    </div>

                    @if(!in_array($estadoActual, ['aprobado', 'rechazado']))
                        <div class="border-t pt-4">
                            <form id="formActualizarEstado" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Estado</label>
                                    <select name="estado" id="estadoSelect" class="w-full rounded-lg border-gray-300" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="aprobado" class="text-emerald-600">Aprobar</option>
                                        <option value="rechazado" class="text-rose-600">Rechazar</option>
                                    </select>
                                </div>
                                <div class="mb-4" id="comentariosDiv" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Comentarios</label>
                                    <textarea name="comentarios" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Motivo del rechazo..."></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                                    <i class="fas fa-save mr-2"></i>Actualizar Estado
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de estados -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-100">
                    <h5 class="font-semibold text-gray-800">
                        <i class="fas fa-history mr-2"></i>Historial de Estados
                    </h5>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @foreach($solicitud->estados as $estado)
                            <div class="border-l-4 pl-3
                                @if($estado->estado == 'aprobado') border-emerald-500
                                @elseif($estado->estado == 'rechazado') border-rose-500
                                @else border-amber-500 @endif">
                                <div class="flex justify-between">
                                    <span class="font-medium">{{ ucfirst($estado->estado) }}</span>
                                    <span class="text-xs text-gray-500">{{ $estado->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($estado->comentarios)
                                    <p class="text-sm text-gray-600 mt-1">{{ $estado->comentarios }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('estadoSelect');
    const comentariosDiv = document.getElementById('comentariosDiv');

    estadoSelect.addEventListener('change', function() {
        if (this.value === 'rechazado') {
            comentariosDiv.style.display = 'block';
        } else {
            comentariosDiv.style.display = 'none';
        }
    });

    const form = document.getElementById('formActualizarEstado');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const estado = estadoSelect.value;
        if (!estado) {
            alert('Por favor selecciona un estado');
            return;
        }

        const comentarios = document.querySelector('textarea[name="comentarios"]')?.value || '';

        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';

        try {
            const response = await fetch('{{ route("admin.solicitudes-pedidos.update-estado", $solicitud->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ estado: estado, comentarios: comentarios })
            });

            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error al actualizar el estado');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al actualizar el estado');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
});
</script>
@endsection
