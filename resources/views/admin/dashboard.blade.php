@extends('layouts.admin.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Tarjeta 1 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-500 bg-opacity-10">
                <i class="fas fa-users text-blue-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Usuarios Totales</p>
                <p class="text-lg font-semibold">1,234</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta 2 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-500 bg-opacity-10">
                <i class="fas fa-box text-green-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Productos</p>
                <p class="text-lg font-semibold">567</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta 3 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-500 bg-opacity-10">
                <i class="fas fa-shopping-cart text-yellow-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Pedidos</p>
                <p class="text-lg font-semibold">89</p>
            </div>
        </div>
    </div>

    <!-- Tarjeta 4 -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-500 bg-opacity-10">
                <i class="fas fa-dollar-sign text-purple-500 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Ventas Hoy</p>
                <p class="text-lg font-semibold">$1,234</p>
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">Actividad Reciente</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">Juan Pérez</td>
                    <td class="px-6 py-4 whitespace-nowrap">Nuevo pedido #1234</td>
                    <td class="px-6 py-4 whitespace-nowrap">Hace 5 minutos</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">María García</td>
                    <td class="px-6 py-4 whitespace-nowrap">Actualizó producto</td>
                    <td class="px-6 py-4 whitespace-nowrap">Hace 15 minutos</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
