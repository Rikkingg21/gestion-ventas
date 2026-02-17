<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    //Handle an incoming request.
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('mensaje', 'Por favor, inicia sesión como administrador.');
        }

        $user = Auth::guard('admin')->user();

        // Verificar si tiene registro en admins
        if (!$user->admin) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'No tienes permisos de administrador.']);
        }

        // Verificar si el admin está activo
        if (!$user->admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Tu cuenta de administrador está desactivada.']);
        }

        return $next($request);
    }
}
