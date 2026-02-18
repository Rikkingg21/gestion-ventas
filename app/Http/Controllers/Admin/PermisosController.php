<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\Module;
use App\Models\Permiso;
use App\Models\AdminPermiso;
use App\Models\StaffPermiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PermisosController extends Controller
{
    // Mostrar la vista principal con la gestión de permisos
    public function index()
    {
        // Obtener todos los admins con sus usuarios y permisos
        $admins = Admin::with(['user', 'permisos.module', 'permisos.permiso', 'permisos.assignedBy.user'])
            ->orderBy('id')
            ->get();

        // Obtener todos los staff con sus usuarios y permisos
        $staffs = Staff::with(['user', 'permisos.module', 'permisos.permiso', 'permisos.assignedBy.user'])
            ->orderBy('id')
            ->get();

        // Obtener todos los módulos activos
        $modules = Module::where('is_active', true)
            ->orderBy('order_position')
            ->get();

        // Obtener todos los permisos (1: crear, 2: leer, 3: actualizar, 4: eliminar)
        $permisos = Permiso::orderBy('id')->get();

        return view('admin.permisos.index', compact('admins', 'staffs', 'modules', 'permisos'));
    }

    // Guardar los cambios de permisos para un admin o staff
    public function save(Request $request)
    {
        try {
            // Validación
            $validator = Validator::make($request->all(), [
                'user_type' => 'required|in:admin,staff',
                'user_id' => 'required|integer',
                'permisos' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', $validator->errors()->all()),
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            if ($request->user_type === 'admin') {
                // Verificar que el admin existe
                $admin = Admin::find($request->user_id);
                if (!$admin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El administrador no existe.'
                    ], 404);
                }

                // No eliminar aún, primero obtener los permisos actuales para comparar
                $permisosActuales = AdminPermiso::where('admin_id', $request->user_id)->get();

                // Convertir los permisos seleccionados a un formato comparable
                $permisosSeleccionados = [];
                if ($request->has('permisos') && is_array($request->permisos)) {
                    foreach ($request->permisos as $permiso) {
                        if (isset($permiso['module_id']) && isset($permiso['permiso_id'])) {
                            $key = $permiso['module_id'] . '_' . $permiso['permiso_id'];
                            $permisosSeleccionados[$key] = [
                                'module_id' => $permiso['module_id'],
                                'permiso_id' => $permiso['permiso_id']
                            ];
                        }
                    }
                }

                // Eliminar permisos que ya no están seleccionados
                foreach ($permisosActuales as $permisoActual) {
                    $key = $permisoActual->module_id . '_' . $permisoActual->permiso_id;
                    if (!isset($permisosSeleccionados[$key])) {
                        $permisoActual->delete();
                    }
                }

                // Agregar nuevos permisos
                foreach ($permisosSeleccionados as $permiso) {
                    $existe = AdminPermiso::where('admin_id', $request->user_id)
                        ->where('module_id', $permiso['module_id'])
                        ->where('permiso_id', $permiso['permiso_id'])
                        ->exists();

                    if (!$existe) {
                        // Verificar que el módulo y permiso existen
                        $moduloExists = Module::where('id', $permiso['module_id'])->exists();
                        $permisoExists = Permiso::where('id', $permiso['permiso_id'])->exists();

                        if ($moduloExists && $permisoExists) {
                            AdminPermiso::create([
                                'admin_id' => $request->user_id,
                                'module_id' => $permiso['module_id'],
                                'permiso_id' => $permiso['permiso_id'],
                                'assigned_by' => Auth::id()
                            ]);
                        }
                    }
                }

            } else {
                // Para staff
                $staff = Staff::find($request->user_id);
                if (!$staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El staff no existe.'
                    ], 404);
                }

                // Obtener permisos actuales
                $permisosActuales = StaffPermiso::where('staff_id', $request->user_id)->get();

                // Convertir los permisos seleccionados a un formato comparable
                $permisosSeleccionados = [];
                if ($request->has('permisos') && is_array($request->permisos)) {
                    foreach ($request->permisos as $permiso) {
                        if (isset($permiso['module_id']) && isset($permiso['permiso_id'])) {
                            $key = $permiso['module_id'] . '_' . $permiso['permiso_id'];
                            $permisosSeleccionados[$key] = [
                                'module_id' => $permiso['module_id'],
                                'permiso_id' => $permiso['permiso_id']
                            ];
                        }
                    }
                }

                // Eliminar permisos que ya no están seleccionados
                foreach ($permisosActuales as $permisoActual) {
                    $key = $permisoActual->module_id . '_' . $permisoActual->permiso_id;
                    if (!isset($permisosSeleccionados[$key])) {
                        $permisoActual->delete();
                    }
                }

                // Agregar nuevos permisos
                foreach ($permisosSeleccionados as $permiso) {
                    $existe = StaffPermiso::where('staff_id', $request->user_id)
                        ->where('module_id', $permiso['module_id'])
                        ->where('permiso_id', $permiso['permiso_id'])
                        ->exists();

                    if (!$existe) {
                        $moduloExists = Module::where('id', $permiso['module_id'])->exists();
                        $permisoExists = Permiso::where('id', $permiso['permiso_id'])->exists();

                        if ($moduloExists && $permisoExists) {
                            StaffPermiso::create([
                                'staff_id' => $request->user_id,
                                'module_id' => $permiso['module_id'],
                                'permiso_id' => $permiso['permiso_id'],
                                'assigned_by' => Auth::id()
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Obtener los permisos actualizados para devolverlos
            $permisosActualizados = $request->user_type === 'admin'
                ? AdminPermiso::where('admin_id', $request->user_id)->get()
                : StaffPermiso::where('staff_id', $request->user_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Permisos guardados correctamente.',
                'data' => $permisosActualizados
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Error de base de datos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error general: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    private function syncAdminPermissions($adminId, $permisosSeleccionados)
    {
        // Obtener permisos actuales
        $permisosActuales = AdminPermiso::where('admin_id', $adminId)->get();

        // Convertir seleccionados a array asociativo
        $nuevosPermisos = [];
        foreach ($permisosSeleccionados as $permiso) {
            if (isset($permiso['module_id']) && isset($permiso['permiso_id'])) {
                $key = $permiso['module_id'] . '_' . $permiso['permiso_id'];
                $nuevosPermisos[$key] = $permiso;
            }
        }

        // Eliminar permisos que ya no están seleccionados
        foreach ($permisosActuales as $permisoActual) {
            $key = $permisoActual->module_id . '_' . $permisoActual->permiso_id;
            if (!isset($nuevosPermisos[$key])) {
                $permisoActual->delete();
            }
        }

        // Agregar nuevos permisos
        foreach ($nuevosPermisos as $permiso) {
            $existe = AdminPermiso::where('admin_id', $adminId)
                ->where('module_id', $permiso['module_id'])
                ->where('permiso_id', $permiso['permiso_id'])
                ->exists();

            if (!$existe) {
                AdminPermiso::create([
                    'admin_id' => $adminId,
                    'module_id' => $permiso['module_id'],
                    'permiso_id' => $permiso['permiso_id'],
                    'assigned_by' => Auth::id()
                ]);
            }
        }
    }

    private function syncStaffPermissions($staffId, $permisosSeleccionados)
    {
        $permisosActuales = StaffPermiso::where('staff_id', $staffId)->get();

        $nuevosPermisos = [];
        foreach ($permisosSeleccionados as $permiso) {
            if (isset($permiso['module_id']) && isset($permiso['permiso_id'])) {
                $key = $permiso['module_id'] . '_' . $permiso['permiso_id'];
                $nuevosPermisos[$key] = $permiso;
            }
        }

        foreach ($permisosActuales as $permisoActual) {
            $key = $permisoActual->module_id . '_' . $permisoActual->permiso_id;
            if (!isset($nuevosPermisos[$key])) {
                $permisoActual->delete();
            }
        }

        foreach ($nuevosPermisos as $permiso) {
            $existe = StaffPermiso::where('staff_id', $staffId)
                ->where('module_id', $permiso['module_id'])
                ->where('permiso_id', $permiso['permiso_id'])
                ->exists();

            if (!$existe) {
                StaffPermiso::create([
                    'staff_id' => $staffId,
                    'module_id' => $permiso['module_id'],
                    'permiso_id' => $permiso['permiso_id'],
                    'assigned_by' => Auth::id()
                ]);
            }
        }
    }

    // Eliminar un permiso específico de un admin o staff
    public function destroy($id, Request $request)
    {
        try {
            $request->validate([
                'user_type' => 'required|in:admin,staff'
            ]);

            if ($request->user_type === 'admin') {
                $permission = AdminPermiso::findOrFail($id);
                $permission->delete();
            } else {
                $permission = StaffPermiso::findOrFail($id);
                $permission->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Permiso eliminado correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar permiso: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener los permisos de un usuario específico
    public function getUserPermissions(Request $request)
    {
        try {
            $request->validate([
                'user_type' => 'required|in:admin,staff',
                'user_id' => 'required|integer',
            ]);

            if ($request->user_type === 'admin') {
                $admin = Admin::with(['permisos.module', 'permisos.permiso', 'permisos.assignedBy.user'])
                    ->find($request->user_id);

                if (!$admin) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Administrador no encontrado'
                    ], 404);
                }

                $permisos = $admin->permisos->map(function($item) {
                    return [
                        'id' => $item->id,
                        'module_id' => $item->module_id,
                        'permiso_id' => $item->permiso_id,
                        'module' => $item->module ? $item->module->name : null,
                        'permiso' => $item->permiso ? $item->permiso->nombre : null,
                        'assigned_by' => $item->assignedBy ? [
                            'id' => $item->assignedBy->id,
                            'name' => $item->assignedBy->user ? $item->assignedBy->user->nombreCompleto : null
                        ] : null,
                        'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : null
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $permisos
                ]);

            } else {
                $staff = Staff::with(['permisos.module', 'permisos.permiso', 'permisos.assignedBy.user'])
                    ->find($request->user_id);

                if (!$staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Staff no encontrado'
                    ], 404);
                }

                $permisos = $staff->permisos->map(function($item) {
                    return [
                        'id' => $item->id,
                        'module_id' => $item->module_id,
                        'permiso_id' => $item->permiso_id,
                        'module' => $item->module ? $item->module->name : null,
                        'permiso' => $item->permiso ? $item->permiso->nombre : null,
                        'assigned_by' => $item->assignedBy ? [
                            'id' => $item->assignedBy->id,
                            'name' => $item->assignedBy->user ? $item->assignedBy->user->nombreCompleto : null
                        ] : null,
                        'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : null
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $permisos
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error al obtener permisos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener el historial de asignaciones de permisos
    public function getPermissionHistory(Request $request)
    {
        try {
            $request->validate([
                'user_type' => 'required|in:admin,staff',
                'user_id' => 'required|integer',
                'days' => 'sometimes|integer|min:1|max:90'
            ]);

            $days = $request->get('days', 30);
            $since = now()->subDays($days);

            if ($request->user_type === 'admin') {
                $history = AdminPermiso::with(['module', 'permiso', 'assignedBy.user'])
                    ->where('admin_id', $request->user_id)
                    ->where('created_at', '>=', $since)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'module' => $item->module ? $item->module->name : null,
                            'permiso' => $item->permiso ? $item->permiso->nombre : null,
                            'assigned_by' => $item->assignedBy && $item->assignedBy->user ?
                                $item->assignedBy->user->nombreCompleto : null,
                            'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : null
                        ];
                    });
            } else {
                $history = StaffPermiso::with(['module', 'permiso', 'assignedBy.user'])
                    ->where('staff_id', $request->user_id)
                    ->where('created_at', '>=', $since)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id' => $item->id,
                            'module' => $item->module ? $item->module->name : null,
                            'permiso' => $item->permiso ? $item->permiso->nombre : null,
                            'assigned_by' => $item->assignedBy && $item->assignedBy->user ?
                                $item->assignedBy->user->nombreCompleto : null,
                            'created_at' => $item->created_at ? $item->created_at->format('d/m/Y H:i') : null
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener historial: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }
}
