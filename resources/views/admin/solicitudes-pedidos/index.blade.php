@extends('layouts.admin.app')

@section('title', 'Solicitudes de Pedidos')
@section('page-title', 'Solicitudes de Pedidos')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Pendientes -->
        <div class="bg-amber-500 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-white text-sm font-medium uppercase">Pendientes</div>
                        <div class="text-white text-3xl font-bold">{{ $estadisticas['pendientes'] ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="fas fa-clock text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aprobadas -->
        <div class="bg-emerald-500 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-white text-sm font-medium uppercase">Aprobadas</div>
                        <div class="text-white text-3xl font-bold">{{ $estadisticas['aprobadas'] ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="fas fa-check-circle text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechazadas -->
        <div class="bg-rose-500 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-white text-sm font-medium uppercase">Rechazadas</div>
                        <div class="text-white text-3xl font-bold">{{ $estadisticas['rechazadas'] ?? 0 }}</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="fas fa-times-circle text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales por Moneda -->
        @forelse($estadisticas['totales_por_moneda'] as $total)
        <div class="bg-sky-500 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-white text-sm font-medium uppercase">Total {{ $total['moneda'] }}</div>
                        <div class="text-white text-2xl font-bold truncate">
                            {{ $total['simbolo'] }} {{ number_format($total['total'], 2) }}
                        </div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="fas fa-sack-dollar text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-gray-500 rounded-lg shadow-lg overflow-hidden">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="text-white text-sm font-medium uppercase">Sin aprobadas</div>
                        <div class="text-white text-2xl font-bold">S/ 0.00</div>
                    </div>
                    <div class="text-white opacity-50">
                        <i class="fas fa-sack-dollar text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-filter mr-2 text-emerald-600"></i>
                Filtros
            </h5>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.solicitudes-pedidos.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                        <option value="">Todos</option>
                        <option value="solicitado" {{ request('estado') == 'solicitado' ? 'selected' : '' }}>Solicitado</option>
                        <option value="aprobado" {{ request('estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                        <option value="rechazado" {{ request('estado') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
                    <select name="metodo_pago" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                        <option value="">Todos</option>
                        @foreach($metodosPago as $metodo)
                            <option value="{{ $metodo->slug }}" {{ request('metodo_pago') == $metodo->slug ? 'selected' : '' }}>
                                {{ $metodo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde', $fechaDesde->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta', $fechaHasta->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                           placeholder="ID, cliente, monto..."
                           class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                </div>

                <div class="col-span-full flex gap-2 mt-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                    <a href="{{ route('admin.solicitudes-pedidos.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition duration-200">
                        <i class="fas fa-redo-alt mr-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de solicitudes -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h5 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-list mr-2 text-emerald-600"></i>
                Lista de Solicitudes
            </h5>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-sm font-medium rounded-full">
                Total: {{ $solicitudes->total() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moneda</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cupón</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Comprobante</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($solicitudes as $solicitud)
                        @php
                            $ultimoEstado = $solicitud->estados->first();
                            $estadoActual = $ultimoEstado ? $ultimoEstado->estado : 'solicitado';

                            $claseEstado = match($estadoActual) {
                                'aprobado' => 'bg-emerald-100 text-emerald-800',
                                'rechazado' => 'bg-rose-100 text-rose-800',
                                default => 'bg-amber-100 text-amber-800'
                            };

                            $totalProductos = $solicitud->carrito ? $solicitud->carrito->productos->sum('cantidad') : 0;
                            $simboloMoneda = $solicitud->moneda ? $solicitud->moneda->simbolo : 'S/';
                            $metodoPago = $solicitud->metodoPago;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">#{{ str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $solicitud->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $solicitud->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $solicitud->cliente->nombres }} {{ $solicitud->cliente->apellido_paterno }}</div>
                                <div class="text-xs text-gray-500">{{ $solicitud->cliente->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($metodoPago)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <i class="fas {{ $metodoPago->icono_class ?? 'fa-credit-card' }} mr-1"></i>
                                        {{ $metodoPago->nombre }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">No especificado</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->moneda)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ $solicitud->moneda->codigo_iso }} ({{ $solicitud->moneda->simbolo }})
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">No especificada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-emerald-600">
                                    {{ $simboloMoneda }} {{ number_format($solicitud->monto, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-sky-100 text-sky-800">
                                    {{ $totalProductos }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->cupon)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ $solicitud->cupon->codigo }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $claseEstado }}">
                                    {{ ucfirst($estadoActual) }}
                                </span>
                                @if($ultimoEstado && $ultimoEstado->comentarios)
                                    <div class="mt-1">
                                        <span class="text-xs text-gray-500 cursor-help" title="{{ $ultimoEstado->comentarios }}">
                                            <i class="fas fa-comment mr-1"></i>Ver
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($solicitud->imagen_1)
                                    @php
                                        $hash = basename($solicitud->imagen_1);
                                    @endphp
                                    <a href="{{ route('admin.solicitudes-pedidos.comprobante', ['hash' => $hash]) }}"
                                    target="_blank"
                                    class="inline-flex items-center px-3 py-1 border border-sky-300 text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg text-sm transition duration-200">
                                        <i class="fas fa-image mr-1"></i>
                                        Ver
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500">Sin comprobante</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if ($currentUser->canUpdate('solicitudes-pedidos'))
                                <a href="{{ route('admin.solicitudes-pedidos.show', $solicitud->id) }}"
                                   class="inline-flex items-center px-3 py-1 border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500">No hay solicitudes para mostrar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($solicitudes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $solicitudes->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
