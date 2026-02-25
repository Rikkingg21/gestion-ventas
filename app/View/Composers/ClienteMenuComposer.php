<?php

namespace App\View\Composers;

use App\Models\Module;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClienteMenuComposer
{
    public function compose(View $view)
    {
        try {
            // Verificar autenticación con guard client
            $user = Auth::guard('client')->user();
            $isAuthenticated = Auth::guard('client')->check();
        } catch (\Exception $e) {
            $user = null;
            $isAuthenticated = false;
            Log::warning('Error al verificar autenticación: ' . $e->getMessage());
        }

        // Si el usuario NO está autenticado, mostrar solo módulos públicos
        if (!$isAuthenticated || !$user) {
            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $publicModules,
                'isAuthenticated' => false,
                'currentUser' => null
            ]);
        }

        // Usuario autenticado - verificar si tiene relación con client
        try {
            // IMPORTANTE: Usar client() en lugar de cliente
            $cliente = $user->client; // Esto carga la relación definida en el modelo User

            // Si el usuario no tiene un registro en clients, mostrar solo módulos públicos
            if (!$cliente) {
                Log::info('Usuario autenticado pero no es cliente: ' . $user->id);

                return $view->with([
                    'menuModules' => collect([]),
                    'publicModules' => $this->getPublicModules(),
                    'isAuthenticated' => true,
                    'currentUser' => $user
                ]);
            }

            // Verificar si el cliente está activo
            if (!$cliente->is_active) {
                Log::info('Cliente inactivo: ' . $user->id);

                // Opcional: podrías forzar el logout aquí si quieres
                // Auth::guard('client')->logout();

                return $view->with([
                    'menuModules' => collect([]),
                    'publicModules' => $this->getPublicModules(),
                    'isAuthenticated' => true,
                    'currentUser' => $user
                ]);
            }

            // Obtener permisos del cliente
            // Nota: Veo que en tu modelo Client no tienes definida la relación 'permisos'
            // Deberías tener algo como:
            // return $this->belongsToMany(Module::class, 'client_permisos')->withPivot('permiso_id');

            // Por ahora, asumamos que no hay permisos específicos para clientes
            // y mostramos todos los módulos públicos
            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => collect([]), // Si no tienes módulos privados para clientes
                'publicModules' => $publicModules,
                'isAuthenticated' => true,
                'currentUser' => $user
            ]);

            // Si en el futuro tienes permisos para clientes, podrías hacer algo como:
            /*
            // Obtener IDs de módulos donde tiene permiso de lectura
            $modulosLecturaIds = $cliente->permisos()
                ->where('permiso_id', 2)
                ->pluck('module_id')
                ->toArray();

            // Obtener módulos padres con sus hijos permitidos
            $modules = Module::with(['children' => function($query) use ($modulosLecturaIds) {
                $query->whereIn('id', $modulosLecturaIds)
                      ->where('is_active', true)
                      ->orderBy('order_position');
            }])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order_position')
            ->get();

            // Filtrar módulos padres
            $modules = $modules->filter(function($module) use ($modulosLecturaIds) {
                $hasDirectPermission = in_array($module->id, $modulosLecturaIds);
                $hasChildrenWithPermission = $module->children->isNotEmpty();
                return $hasDirectPermission || $hasChildrenWithPermission;
            });

            return $view->with([
                'menuModules' => $modules,
                'publicModules' => $this->getPublicModules(),
                'isAuthenticated' => true,
                'currentUser' => $user
            ]);
            */

        } catch (\Exception $e) {
            Log::error('Error al obtener datos del cliente: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $this->getPublicModules(),
                'isAuthenticated' => true,
                'currentUser' => $user
            ]);
        }
    }

    // Obtener módulos públicos
    private function getPublicModules()
    {
        try {
            return Module::where('is_active', true)
                ->where('is_public', true)
                ->with(['children' => function($query) {
                    $query->where('is_active', true)
                          ->where('is_public', true)
                          ->orderBy('order_position');
                }])
                ->whereNull('parent_id')
                ->orderBy('order_position')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener módulos públicos: ' . $e->getMessage());
            return collect([]);
        }
    }
}
