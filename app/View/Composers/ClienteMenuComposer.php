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
            // Intentar verificar autenticación con guard cliente
            $user = Auth::guard('cliente')->user();
        } catch (\Exception $e) {
            // Si el guard no existe, asumimos que no hay usuario autenticado
            $user = null;
            Log::warning('Guard cliente no configurado, mostrando solo módulos públicos');
        }

        // Si el usuario NO está autenticado, mostrar módulos públicos
        if (!$user || !isset($user->cliente) || !$user->cliente) {
            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $publicModules,
                'isAuthenticated' => false
            ]);
        }

        // Usuario autenticado - obtener módulos con permisos
        try {
            $cliente = $user->cliente;

            // Obtener IDs de módulos donde tiene permiso de lectura (permiso_id = 2)
            $modulosLecturaIds = $cliente->permisos()
                ->where('permiso_id', 2)
                ->pluck('module_id')
                ->toArray();

            // Si no tiene permisos de lectura, solo mostrar módulos públicos
            if (empty($modulosLecturaIds)) {
                $publicModules = $this->getPublicModules();

                return $view->with([
                    'menuModules' => collect([]),
                    'publicModules' => $publicModules,
                    'isAuthenticated' => true
                ]);
            }

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

            // Filtrar módulos padres que tengan permiso directo o hijos con permiso
            $modules = $modules->filter(function($module) use ($modulosLecturaIds) {
                $hasDirectPermission = in_array($module->id, $modulosLecturaIds);
                $hasChildrenWithPermission = $module->children->isNotEmpty();

                return $hasDirectPermission || $hasChildrenWithPermission;
            });

            // Limpiar hijos no permitidos
            $modules->each(function($module) use ($modulosLecturaIds) {
                if ($module->children->isNotEmpty()) {
                    $hijosPermitidos = $module->children->whereIn('id', $modulosLecturaIds);
                    $module->setRelation('children', $hijosPermitidos);
                }
            });

            // También obtener módulos públicos
            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => $modules,
                'publicModules' => $publicModules,
                'isAuthenticated' => true,
                'currentUser' => $user
            ]);

        } catch (\Exception $e) {
            // Si hay error al obtener permisos, mostrar solo módulos públicos
            Log::error('Error al obtener permisos de cliente: ' . $e->getMessage());

            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $publicModules,
                'isAuthenticated' => true
            ]);
        }
    }

    /**
     * Obtener módulos públicos
     */
    private function getPublicModules()
    {
        return Module::active()
            ->where('is_public', true)
            ->with(['children' => function($query) {
                $query->active()
                      ->where('is_public', true)
                      ->orderBy('order_position');
            }])
            ->whereNull('parent_id')
            ->orderBy('order_position')
            ->get();
    }
}
