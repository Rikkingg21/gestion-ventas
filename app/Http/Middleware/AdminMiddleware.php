<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    //Handle an incoming request.
    public function handle(Request $request, Closure $next, $permission = null, $module = null)
    {
        // Verificar autenticación
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

        // Si se requiere un permiso específico
        if ($permission) {
            $permisosMap = [
                'crear' => 1,
                'leer' => 2,
                'actualizar' => 3,
                'eliminar' => 4
            ];

            $permisoId = $permisosMap[$permission] ?? null;

            if (!$permisoId) {
                abort(403, 'Permiso no válido');
            }

            // Si no se especifica módulo, intentar obtenerlo de la ruta
            if (!$module) {
                $routeName = $request->route()->getName();
                $parts = explode('.', $routeName);
                $module = $parts[1] ?? null; // admin.permisos.index -> permisos
            }

            // Verificar permiso
            if (!$user->isSuperAdmin()) {
                if (!$module || !$user->hasPermission($module, $permisoId)) {
                    // Para solicitudes AJAX
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No tienes permiso para realizar esta acción.'
                        ], 403);
                    }

                    abort(403, 'No tienes permiso para acceder a esta página.');
                }
            }
        }

        return $next($request);
    }
}
