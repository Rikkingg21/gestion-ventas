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
            // Para admin normal, obtener módulos donde tiene permiso de lectura
            $modules = Module::with(['children' => function($query) use ($user) {
                // Filtrar hijos donde tiene permiso de lectura
                $query->whereHas('permisos', function($q) use ($user) {
                    $q->where('admin_id', $user->admin->id)
                      ->where('permiso_id', 2); // permiso_id 2 = leer
                })->orWhereHas('children.permisos', function($q) use ($user) {
                    // O incluir padres que tengan hijos con permisos
                    $q->where('admin_id', $user->admin->id)
                      ->where('permiso_id', 2);
                });
            }])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order_position')
            ->get();

            // Filtrar solo módulos padres que tengan al menos un hijo con permiso
            // o que ellos mismos tengan permiso de lectura
            $modules = $modules->filter(function($module) use ($user) {
                // Verificar si el padre tiene permiso directo
                $hasParentPermission = $module->permisos()
                    ->where('admin_id', $user->admin->id)
                    ->where('permiso_id', 2)
                    ->exists();

                // Verificar si tiene hijos con permiso
                $hasChildrenWithPermission = $module->children->isNotEmpty();

                return $hasParentPermission || $hasChildrenWithPermission;
            });
        }

        $view->with('menuModules', $modules);
    }
}
