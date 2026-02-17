<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Obtener el usuario autenticado (que es un User)
        $user = Auth::guard('admin')->user();

        // Obtener el admin relacionado
        $admin = $user->admin;

        // Verificar que realmente sea un admin
        if (!$admin) {
            abort(403, 'No tienes permisos de administrador');
        }

        // Obtener módulos con permisos para este admin
        $modules = Module::with(['children' => function($q) {
                $q->active()->orderBy('order_position');
            }])
            ->active()
            ->parents()
            ->orderBy('order_position')
            ->get()
            ->filter(function($module) use ($admin) {
                // Si es superadmin, ve todos los módulos
                if ($admin->isSuperAdmin()) {
                    return true;
                }

                // Filtrar módulos donde tiene permisos
                // Nota: Necesitas implementar la lógica de permisos aquí
                // Por ahora, retornamos true para probar
                return true;

                /* Cuando tengas permisos implementados:
                return $admin->permissions()
                    ->whereHas('module', function($q) use ($module) {
                        $q->where('id', $module->id)
                          ->orWhereIn('id', $module->children->pluck('id'));
                    })->exists();
                */
            });

        return view('admin.dashboard', compact('modules'));
    }
}
