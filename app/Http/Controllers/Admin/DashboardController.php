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
        $admin = Auth::guard('admin')->user();

        // Obtener módulos con permisos para este admin
        $modules = Module::with(['children' => function($q) {
                $q->active()->orderBy('order_position');
            }])
            ->active()
            ->parents()
            ->get()
            ->filter(function($module) use ($admin) {
                // Si es superadmin, ve todos los módulos
                if ($admin->isSuperAdmin()) {
                    return true;
                }

                // Filtrar módulos donde tiene permisos
                return $admin->permissions()
                    ->whereHas('module', function($q) use ($module) {
                        $q->where('id', $module->id)
                          ->orWhereIn('id', $module->children->pluck('id'));
                    })->exists();
            });

        return view('admin.dashboard', compact('modules'));
    }
}
