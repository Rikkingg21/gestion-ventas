<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Module;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, $permission = null, $moduleSlug = null)
    {
        // Verificar autenticación
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('mensaje', 'Por favor, inicia sesión como administrador.');
        }

        $user = Auth::guard('admin')->user();
        $admin = $user->admin;

        // Verificar si tiene registro en admins
        if (!$admin) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'No tienes permisos de administrador.']);
        }

        // Verificar si el admin está activo
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['username' => 'Tu cuenta de administrador está desactivada.']);
        }

        // Si es superadmin, permitir todo sin verificar permisos
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Si no se requiere permiso específico, solo verificar autenticación
        if (!$permission) {
            return $next($request);
        }

        // Mapeo de permisos
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

        // Obtener el ID del módulo basado en el slug
        $moduleId = null;

        // Si se proporcionó un slug de módulo en el middleware
        if ($moduleSlug) {
            // Buscar el módulo por su slug
            $module = Module::where('slug', $moduleSlug)->first();
            if (!$module) {
                abort(403, "Módulo con slug '{$moduleSlug}' no encontrado");
            }
            $moduleId = $module->id;
        } else {
            // Intentar obtener el slug de la URL
            $path = $request->path();
            $path = preg_replace('#^admin/#', '', $path);
            $segments = explode('/', $path);
            $urlSlug = $segments[0] ?? null;

            // Excepciones para rutas especiales que no requieren módulo
            $specialRoutes = ['dashboard', 'perfil', 'profile', 'logout'];
            if (in_array($urlSlug, $specialRoutes)) {
                return $next($request);
            }

            // Buscar el módulo por el slug de la URL
            if ($urlSlug) {
                $module = Module::where('slug', $urlSlug)->first();
                if ($module) {
                    $moduleId = $module->id;
                } else {
                    // Intentar buscar por nombre si no encuentra por slug
                    $module = Module::where('name', $urlSlug)->first();
                    if ($module) {
                        $moduleId = $module->id;
                    }
                }
            }
        }

        // Si no se pudo determinar el módulo, mostrar error con información útil
        if (!$moduleId) {
            if (config('app.debug')) {
                abort(403, "No se pudo determinar el módulo. Ruta: " . $request->path() . ", Slug recibido: " . ($moduleSlug ?? 'ninguno'));
            }
            abort(403, 'No se pudo determinar el módulo para verificar permisos.');
        }

        // Verificar el permiso usando el modelo Admin directamente
        if (!$admin->tienePermiso($moduleId, $permisoId)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "No tienes permiso para '{$permission}' en este módulo."
                ], 403);
            }

            abort(403, "No tienes permiso para '{$permission}' en este módulo.");
        }

        return $next($request);
    }
}
