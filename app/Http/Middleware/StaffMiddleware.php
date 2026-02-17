<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('staff')->check()) {
            return redirect()->route('staff.login')
                ->with('mensaje', 'Por favor, inicia sesión como staff.');
        }

        $user = Auth::guard('staff')->user();

        // Verificar si tiene registro en staff
        if (!$user->staff) {
            Auth::guard('admin')->logout();
            return redirect()->route('staff.login')
                ->withErrors(['username' => 'No tienes permisos de staff.']);
        }

        // Verificar si el admin está activo
        if (!$user->staff->is_active) {
            Auth::guard('staff')->logout();
            return redirect()->route('staff.login')
                ->withErrors(['username' => 'Tu cuenta de staff está desactivada.']);
        }

        return $next($request);
    }
}
