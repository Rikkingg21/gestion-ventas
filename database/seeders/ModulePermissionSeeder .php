<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;

class ModulePermissionSeeder extends Seeder
{
    public function run()
    {
        // Módulos principales
        $modules = [
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'icon' => 'bi-speedometer2',
                'route' => 'admin.dashboard',
                'order_position' => 1,
                'permissions' => ['view']
            ],
            [
                'name' => 'Usuarios',
                'slug' => 'users',
                'icon' => 'bi-people',
                'route' => 'admin.users.index',
                'order_position' => 2,
                'permissions' => ['view', 'create', 'edit', 'delete']
            ],
            [
                'name' => 'Clientes',
                'slug' => 'clients',
                'icon' => 'bi-person-badge',
                'route' => 'admin.clients.index',
                'order_position' => 3,
                'permissions' => ['view', 'create', 'edit', 'delete']
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'icon' => 'bi-person-workspace',
                'route' => 'admin.staff.index',
                'order_position' => 4,
                'permissions' => ['view', 'create', 'edit', 'delete']
            ],
            [
                'name' => 'Módulos y Permisos',
                'slug' => 'modules',
                'icon' => 'bi-grid-3x3-gap-fill',
                'route' => 'admin.modules.index',
                'order_position' => 5,
                'permissions' => ['view', 'create', 'edit', 'delete']
            ],
            [
                'name' => 'Reportes',
                'slug' => 'reports',
                'icon' => 'bi-file-earmark-bar-graph',
                'route' => 'admin.reports.index',
                'order_position' => 6,
                'permissions' => ['view', 'export']
            ],
            [
                'name' => 'Configuración',
                'slug' => 'settings',
                'icon' => 'bi-gear',
                'route' => 'admin.settings',
                'order_position' => 7,
                'permissions' => ['view', 'edit']
            ],
        ];

        foreach ($modules as $moduleData) {
            $permissions = $moduleData['permissions'];
            unset($moduleData['permissions']);

            $module = Module::create($moduleData);

            // Crear permisos para el módulo
            foreach ($permissions as $permission) {
                $permissionNames = [
                    'view' => 'Ver',
                    'create' => 'Crear',
                    'edit' => 'Editar',
                    'delete' => 'Eliminar',
                    'export' => 'Exportar'
                ];

                Permission::create([
                    'module_id' => $module->id,
                    'name' => $permissionNames[$permission] . ' ' . $module->name,
                    'slug' => $module->slug . '.' . $permission,
                    'description' => 'Permiso para ' . $permissionNames[$permission] . ' ' . $module->name
                ]);
            }
        }
    }
}
