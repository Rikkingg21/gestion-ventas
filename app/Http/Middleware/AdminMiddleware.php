<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Module;

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

            // Si no se especifica módulo, obtenerlo de la URL actual
            if (!$module) {
                // Obtener la ruta actual (ej: /admin/users)
                $path = $request->path();

                // Eliminar el prefijo 'admin/' si existe
                if (str_starts_with($path, 'admin/')) {
                    $path = substr($path, 6); // Eliminar 'admin/'
                }

                // Obtener el primer segmento de la ruta (users, productos, permisos, etc.)
                $segments = explode('/', $path);
                $moduleSlug = $segments[0] ?? null;

                // Buscar el módulo en la base de datos por su slug o ruta
                $module = $moduleSlug;

                // Para debugging
                \Log::info('Verificando permiso', [
                    'permission' => $permission,
                    'module_from_url' => $moduleSlug,
                    'full_path' => $request->path(),
                    'user' => $user->username
                ]);
            }

            // Verificar permiso
            if (!$user->isSuperAdmin()) {
                if (!$module) {
                    \Log::error('No se pudo determinar el módulo', [
                        'path' => $request->path(),
                        'url' => $request->url()
                    ]);
                    abort(403, 'No se pudo determinar el módulo para verificar permisos.');
                }

                // Verificar si el módulo existe en la base de datos
                $moduleExists = Module::where('slug', $module)
                    ->orWhere('route', '/' . $module)
                    ->exists();

                if (!$moduleExists) {
                    \Log::warning('Módulo no encontrado en BD', [
                        'module' => $module,
                        'path' => $request->path()
                    ]);
                    // Si el módulo no existe, permitir el acceso (para rutas que no requieren permisos)
                    return $next($request);
                }

                if (!$user->hasPermission($module, $permisoId)) {
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
