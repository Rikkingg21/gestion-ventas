@extends('layouts.admin.app')

@section('title', 'Nueva Moneda')
@section('page-title', 'Crear Nueva Moneda')

@section('content')
<div class="bg-white rounded-lg shadow-lg">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Datos de la Moneda</h3>
    </div>

    <form action="{{ route('admin.moneda.store') }}" method="POST" class="p-6">
        @csrf

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                <div class="font-medium">Por favor corrige los siguientes errores:</div>
                <ul class="mt-2 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="pais" class="block text-sm font-medium text-gray-700 mb-2">
                    País <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="pais"
                       id="pais"
                       value="{{ old('pais') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Ej: Estados Unidos, México, Argentina</p>
            </div>

            <div>
                <label for="pais_code" class="block text-sm font-medium text-gray-700 mb-2">
                    Código del País
                </label>
                <input type="text"
                       name="pais_code"
                       id="pais_code"
                       value="{{ old('pais_code') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Ej: US, MX, AR">
                <p class="text-xs text-gray-500 mt-1">Código ISO de 2 letras (para ver los codigos oficilaes click
                    <a href="https://es.wikipedia.org/wiki/ISO_3166-1_alfa-2" target="_blank" class="text-indigo-600 hover:underline">aquí</a>
                    )</p>
            </div>

            <div>
                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre de la Moneda <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nombre"
                       id="nombre"
                       value="{{ old('nombre') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Ej: Dólar Estadounidense">
            </div>

            <div>
                <label for="codigo_iso" class="block text-sm font-medium text-gray-700 mb-2">
                    Código ISO <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="codigo_iso"
                       id="codigo_iso"
                       value="{{ old('codigo_iso') }}"
                       required
                       maxlength="3"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 uppercase"
                       placeholder="Ej: USD, EUR, MXN">
                <p class="text-xs text-gray-500 mt-1">Código ISO 4217 de 3 caracteres (para ver cuales son los codigos click
                    <a href="https://es.iban.com/currency-codes" target="_blank" class="text-indigo-600 hover:underline">aquí</a>
                    )</p>
            </div>

            <div>
                <label for="simbolo" class="block text-sm font-medium text-gray-700 mb-2">
                    Símbolo <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="simbolo"
                       id="simbolo"
                       value="{{ old('simbolo') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Ej: $, €, MX$">
            </div>

            <div>
                <label for="tasa_cambio_usd" class="block text-sm font-medium text-gray-700 mb-2">
                    Tasa de Cambio (USD) <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       name="tasa_cambio_usd"
                       id="tasa_cambio_usd"
                       value="{{ old('tasa_cambio_usd') }}"
                       required
                       step="0.01"
                       min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Ej: 1.00">
                <p class="text-xs text-gray-500 mt-1">Valor de 1 USD en esta moneda</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center">
                        <input type="radio"
                               name="is_active"
                               value="1"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                               class="form-radio text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2">Activo</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio"
                               name="is_active"
                               value="0"
                               {{ old('is_active') == '0' ? 'checked' : '' }}
                               class="form-radio text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2">Inactivo</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Aceptar Pagos</label>
                <div class="flex items-center">
                    <label class="inline-flex items-center">
                        <input type="checkbox"
                               name="aceptar_pagos"
                               value="1"
                               {{ old('aceptar_pagos') ? 'checked' : '' }}
                               class="form-checkbox text-indigo-600 focus:ring-indigo-500 rounded">
                        <span class="ml-2">Permitir pagos en esta moneda</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-1">Activa esta opción si quieres aceptar pagos en esta moneda</p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.moneda.index') }}"
               class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i>
                Guardar Moneda
            </button>
        </div>
    </form>
</div>
@endsection
