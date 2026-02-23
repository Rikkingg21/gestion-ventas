<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next, $permission = null, $moduleId = null)
    {
        // Verificar autenticación
        if (!Auth::guard('staff')->check()) {
            return redirect()->route('staff.login')
                ->with('mensaje', 'Por favor, inicia sesión como staff.');
        }

        $user = Auth::guard('staff')->user();

        // Verificar si tiene registro en staff
        if (!$user->staff) {
            Auth::guard('staff')->logout();
            return redirect()->route('staff.login')
                ->withErrors(['username' => 'No tienes permisos de staff.']);
        }

        // Verificar si el staff está activo
        if (!$user->staff->is_active) {
            Auth::guard('staff')->logout();
            return redirect()->route('staff.login')
                ->withErrors(['username' => 'Tu cuenta de staff está desactivada.']);
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

            // Si no se especificó moduleId, intentar obtenerlo de la URL
            if (!$moduleId) {
                // Obtener el path actual
                $path = $request->path();

                // Eliminar el prefijo 'staff/' si existe
                if (str_starts_with($path, 'staff/')) {
                    $path = substr($path, 6);
                }

                // Obtener el primer segmento
                $segments = explode('/', $path);
                $urlModule = $segments[0] ?? null;

                // Si es dashboard, permitir acceso
                if ($urlModule === 'dashboard') {
                    return $next($request);
                }

                // Si no podemos determinar el módulo, abortar
                abort(403, 'No se especificó el ID del módulo para verificar permisos.');
            }

            // Verificar que moduleId sea un número válido
            if (!is_numeric($moduleId)) {
                abort(403, 'El ID del módulo debe ser un número.');
            }

            // Verificar si tiene el permiso específico usando SOLO IDs
            if (!$user->hasPermission((int)$moduleId, $permisoId)) {
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

        return $next($request);
    }
}
