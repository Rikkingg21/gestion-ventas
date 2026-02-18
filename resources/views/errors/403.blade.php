@extends('layouts.admin.app')

@section('title', 'Acceso Denegado')
@section('page-title', 'Error 403')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="text-9xl font-bold text-indigo-200">403</div>
        <h1 class="text-4xl font-bold text-gray-800 mt-4">¡Acceso Denegado!</h1>
        <p class="text-gray-600 mt-2">No tienes permisos suficientes para acceder a esta página.</p>
        <p class="text-sm text-gray-500 mt-1">{{ $exception->getMessage() ?? 'Se requiere un permiso específico.' }}</p>
        <a href="{{ route('admin.dashboard') }}"
           class="inline-block mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-home mr-2"></i>
            Volver al Dashboard
        </a>
    </div>
</div>
@endsection
