@extends('layouts.staff.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<h2>Hola Staff</h2>
<form method="POST" action="{{ route('staff.logout') }}">
    @csrf
    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
        Cerrar Sesión
    </button>
</form>
@endsection
