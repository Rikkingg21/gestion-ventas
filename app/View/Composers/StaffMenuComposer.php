<?php

namespace App\View\Composers;

use App\Models\Module;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class StaffMenuComposer
{
    public function compose(View $view)
    {
        $user = Auth::guard('staff')->user();

        if (!$user) {
            return $view->with('menuModules', collect([]));
        }

        // Para staff, obtener módulos donde tiene permiso de lectura
        $modules = Module::with(['children' => function($query) use ($user) {
            // Filtrar hijos donde tiene permiso de lectura
            $query->whereHas('staffPermisos', function($q) use ($user) {
                $q->where('staff_id', $user->id)
                  ->where('permiso_id', 2); // permiso_id 2 = leer
            })->orWhereHas('children.staffPermisos', function($q) use ($user) {
                // O incluir padres que tengan hijos con permisos
                $q->where('staff_id', $user->id)
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
            $hasParentPermission = $module->staffPermisos()
                ->where('staff_id', $user->id)
                ->where('permiso_id', 2)
                ->exists();

            // Verificar si tiene hijos con permiso
            $hasChildrenWithPermission = $module->children->isNotEmpty() &&
                $module->children->contains(function($child) use ($user) {
                    return $child->staffPermisos()
                        ->where('staff_id', $user->id)
                        ->where('permiso_id', 2)
                        ->exists();
                });

            return $hasParentPermission || $hasChildrenWithPermission;
        });

        $view->with('menuModules', $modules);
    }
}
