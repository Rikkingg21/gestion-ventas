@extends('layouts.cliente.app')

@section('title', 'Mi Contenido: ' . $producto->nombre)
@section('page-title', 'Visualizando: ' . $producto->nombre)

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        {{-- Cabecera --}}
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">
                        <i class="fab fa-google-drive me-2"></i>
                        {{ $producto->nombre }}
                    </h4>
                    <p class="mb-0 small opacity-75">
                        Compra #{{ $solicitud->id }} - {{ $solicitud->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('client.compras.show', $solicitud->id) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    @if($driveEmbedUrl)
                    <a href="{{ $driveEmbedUrl }}" target="_blank" class="btn btn-light btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i> Nueva ventana
                    </a>
                    @endif
                    <button type="button"
                        class="btn btn-light btn-sm"
                        id="ayudaPopover"
                        data-bs-toggle="popover"
                        data-bs-trigger="click"
                        data-bs-placement="bottom"
                        data-bs-html="true"
                        data-bs-title="<i class='fas fa-question-circle me-1'></i> Ayuda - Solución de problemas"
                        data-bs-content="<div class='text-start'>
                            <p class='mb-2'><strong>¿Problemas para visualizar?</strong></p>
                            <p class='mb-2 small'>Si tienes un error 504 o 500, puedes limpiar las cookies de Google Drive haciendo click en el botón de abajo:</p>
                            <button type='button' class='btn btn-warning btn-sm w-100' onclick='limpiarCookiesDrive(); cerrarPopover();'>
                                <i class='fas fa-trash-alt me-1'></i>
                                Limpiar cookies de Drive
                            </button>
                            <hr class='my-2'>
                            <p class='mb-0 small text-muted'>
                                <i class='fas fa-info-circle'></i> Si el problema persiste, contacta con un administrador.
                            </p>
                        </div>">
                        <i class="fas fa-question-circle"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Reproductor de Google Drive --}}
        <div class="card-body p-0">
            @if($driveEmbedUrl)
                <div class="ratio ratio-16x9">
                    <iframe
                        src="{{ $driveEmbedUrl }}"
                        allowfullscreen>
                    </iframe>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h5>Contenido no disponible</h5>
                    <p class="text-muted">El archivo o carpeta para "{{ $producto->nombre }}" no está configurado correctamente.</p>
                    <a href="{{ route('client.compras.show', $solicitud->id) }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Inicializar popovers
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            html: true,
            sanitize: false
        })
    });
});

// Función para cerrar el popover
function cerrarPopover() {
    var popoverTrigger = document.getElementById('ayudaPopover');
    var popover = bootstrap.Popover.getInstance(popoverTrigger);
    if (popover) {
        popover.hide();
    }
}

function limpiarCookiesDrive() {
    // Mostrar confirmación antes de limpiar
    if (confirm('¿Estás seguro de que deseas limpiar las cookies de Google Drive? La página se recargará.')) {
        // Limpiar cookies relacionadas con Google Drive
        document.cookie.split(";").forEach(function(c) {
            if (c.trim().startsWith('GDrive') ||
                c.trim().startsWith('google') ||
                c.trim().includes('drive')) {
                document.cookie = c.trim().split("=")[0] + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.google.com";
                document.cookie = c.trim().split("=")[0] + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            }
        });

        // Recargar la página
        location.reload();
    }
}
</script>

@endsection
