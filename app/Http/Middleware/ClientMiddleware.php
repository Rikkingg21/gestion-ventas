<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('client')->check()) {
            // Si está autenticado pero no es cliente (no tiene registro en clients)
            if (!Auth::guard('client')->user()->client) {
                Auth::guard('client')->logout();
                return redirect()->route('client.login')
                    ->withErrors(['error' => 'No tienes permisos de cliente.']);
            }

            return $next($request);
        }

        // Si no está autenticado, redirigir a una página personalizada
        return redirect()->route('client.login')
            ->with('info', 'Por favor, inicia sesión para acceder al dashboard.');
    }
}
