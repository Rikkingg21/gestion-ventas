<?php

namespace App\View\Composers;

use App\Models\Module;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class StaffMenuComposer
{
    public function compose(View $view)
    {
        // Verificar autenticación con guard staff
        $user = Auth::guard('staff')->user();

        if (!$user || !$user->staff) {
            return $view->with('menuModules', collect([]));
        }

        $staff = $user->staff;

        // Obtener IDs de módulos donde tiene permiso de lectura (permiso_id = 2)
        $modulosLecturaIds = $staff->permisos()
            ->where('permiso_id', 2)
            ->pluck('module_id')
            ->toArray();

        // Si no tiene permisos de lectura, retornar colección vacía
        if (empty($modulosLecturaIds)) {
            return $view->with('menuModules', collect([]));
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

        // Limpiar hijos no permitidos (por si acaso)
        $modules->each(function($module) use ($modulosLecturaIds) {
            if ($module->children->isNotEmpty()) {
                $hijosPermitidos = $module->children->whereIn('id', $modulosLecturaIds);
                $module->setRelation('children', $hijosPermitidos);
            }
        });

        $view->with('menuModules', $modules);
    }
}
