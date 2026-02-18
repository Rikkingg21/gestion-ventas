<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    // Mostrar lista de asignaciones
    public function index()
    {
        $admins = Admin::with(['user', 'permissions' => function($q) {
            $q->with('module')->wherePivot('expires_at', '>', now())->orWhereNull('expires_at');
        }])->get();

        $modules = Module::with(['permissions'])->get();

        return view('admin.assignments.index', compact('admins', 'modules'));
    }

    // Mostrar formulario de asignación
    public function create()
    {
        $admins = Admin::with('user')->get();
        $modules = Module::with('permissions')->get();

        return view('admin.assignments.create', compact('admins', 'modules'));
    }

    // Guardar una nueva asignación
    public function store(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'permission_id' => 'required|exists:permissions,id',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:500'
        ]);

        $admin = Admin::find($request->admin_id);

        // Verificar si ya tiene el permiso
        if ($admin->permissions()->where('permission_id', $request->permission_id)->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Este permiso ya está asignado a este administrador.');
        }

        // Asignar usando la relación many-to-many con datos adicionales
        $admin->permissions()->attach($request->permission_id, [
            'assigned_by' => Auth::guard('admin')->id(),
            'assigned_at' => now(),
            'expires_at' => $request->expires_at,
            'notes' => $request->notes,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()
            ->route('admin.assignments.index')
            ->with('success', 'Permiso asignado correctamente.');
    }

    // Mostrar detalles de un admin y sus permisos
    public function show(Admin $admin)
    {
        $admin->load(['user', 'permissions' => function($q) {
            $q->with('module')->withPivot(['assigned_by', 'assigned_at', 'expires_at', 'notes']);
        }]);

        $modules = Module::with('permissions')->get();

        return view('admin.assignments.show', compact('admin', 'modules'));
    }

    // Eliminar una asignación
    public function destroy($adminPermissionId)
    {
        // Buscar en la tabla pivot directamente
        $pivot = \DB::table('admin_permissions')->where('id', $adminPermissionId)->first();

        if (!$pivot) {
            return redirect()
                ->route('admin.assignments.index')
                ->with('error', 'Asignación no encontrada.');
        }

        $permission = Permission::find($pivot->permission_id);
        $admin = Admin::with('user')->find($pivot->admin_id);

        // Eliminar usando la relación
        Admin::find($pivot->admin_id)->permissions()->detach($pivot->permission_id);

        return redirect()
            ->route('admin.assignments.index')
            ->with('success', "Permiso '{$permission->name}' removido de {$admin->user->name}");
    }

    // Asignación masiva de permisos
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:admins,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
            'expires_at' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:500'
        ]);

        $admin = Admin::find($request->admin_id);
        $assignedBy = Auth::guard('admin')->id();
        $count = 0;

        // Obtener permisos actuales para evitar duplicados
        $existingPermissions = $admin->permissions()->pluck('permission_id')->toArray();

        $newPermissions = [];
        foreach ($request->permissions as $permissionId) {
            if (!in_array($permissionId, $existingPermissions)) {
                $newPermissions[$permissionId] = [
                    'assigned_by' => $assignedBy,
                    'assigned_at' => now(),
                    'expires_at' => $request->expires_at,
                    'notes' => $request->notes,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $count++;
            }
        }

        if (!empty($newPermissions)) {
            $admin->permissions()->attach($newPermissions);
        }

        return redirect()
            ->route('admin.assignments.show', $admin)
            ->with('success', "{$count} permiso(s) asignados correctamente a {$admin->user->name}");
    }
}
