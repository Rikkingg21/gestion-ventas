<?php

namespace App\View\Composers;

use App\Models\Module;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AdminMenuComposer
{
    public function compose(View $view)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $view->with('menuModules', collect([]));
        }

        // Si es super admin, obtiene todos los módulos activos
        if ($user->isSuperAdmin()) {
            $modules = Module::with('children')
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('order_position')
                ->get();
        } else {
            // Obtener el admin relacionado
            $admin = $user->admin;

            if (!$admin) {
                return $view->with('menuModules', collect([]));
            }

            // Obtener IDs de módulos donde tiene permiso de lectura
            $modulosLecturaIds = $admin->permisos()
                ->where('permiso_id', 2) // permiso_id 2 = leer
                ->pluck('module_id')
                ->toArray();

            // Obtener módulos padres que tengan hijos con permiso de lectura
            $modules = Module::with(['children' => function($query) use ($modulosLecturaIds) {
                $query->whereIn('id', $modulosLecturaIds)
                      ->orderBy('order_position');
            }])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order_position')
            ->get();

            // Filtrar solo módulos padres que:
            // 1. Tengan permiso directo de lectura, o
            // 2. Tengan al menos un hijo con permiso de lectura
            $modules = $modules->filter(function($module) use ($modulosLecturaIds) {
                $hasDirectPermission = in_array($module->id, $modulosLecturaIds);
                $hasChildrenWithPermission = $module->children->isNotEmpty();

                return $hasDirectPermission || $hasChildrenWithPermission;
            });

            // Para cada módulo, cargar solo los hijos con permiso
            $modules->each(function($module) use ($modulosLecturaIds) {
                if ($module->children->isNotEmpty()) {
                    $module->setRelation(
                        'children',
                        $module->children->whereIn('id', $modulosLecturaIds)
                    );
                }
            });
        }

        $view->with('menuModules', $modules);
    }
}
