<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use SoftDeletes;

    protected $table = 'admins';
    protected $fillable = ['user_id', 'nivel', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSuperAdmin()
    {
        return $this->nivel === 'super_admin';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function permisos()
    {
        return $this->hasMany(AdminPermiso::class);
    }

    // Verificar si tiene un permiso específico en un módulo
    public function tienePermiso($moduleId, $permisoId)
    {
        return $this->permisos()
            ->where('module_id', $moduleId)
            ->where('permiso_id', $permisoId)
            ->exists();
    }

    // Verificar si tiene permiso de lectura en un módulo
    public function puedeLeer($moduleId)
    {
        return $this->tienePermiso($moduleId, 2); // permiso_id 2 = leer
    }

    // Obtener todos los permisos del admin agrupados por módulo
    public function getPermisosAgrupados()
    {
        return $this->permisos()
            ->with(['module', 'permiso'])
            ->get()
            ->groupBy('module.name');
    }

    // Obtener IDs de módulos donde tiene permiso de lectura
    public function getModulosLecturaIds()
    {
        return $this->permisos()
            ->where('permiso_id', 2)
            ->pluck('module_id')
            ->toArray();
    }

    // Asignar permiso a admin
    public function asignarPermiso($moduleId, $permisoId)
    {
        return $this->permisos()->firstOrCreate([
            'module_id' => $moduleId,
            'permiso_id' => $permisoId
        ]);
    }

    // Asignar múltiples permisos
    public function asignarPermisos($moduleId, array $permisosIds)
    {
        foreach ($permisosIds as $permisoId) {
            $this->asignarPermiso($moduleId, $permisoId);
        }
    }

    // Quitar permiso
    public function quitarPermiso($moduleId, $permisoId)
    {
        return $this->permisos()
            ->where('module_id', $moduleId)
            ->where('permiso_id', $permisoId)
            ->delete();
    }

    // Quitar todos los permisos de un módulo
    public function quitarPermisosModulo($moduleId)
    {
        return $this->permisos()
            ->where('module_id', $moduleId)
            ->delete();
    }

    // Sincronizar permisos (reemplaza todos los existentes)
    public function syncPermisos(array $permisosData)
    {
        // Eliminar permisos existentes
        $this->permisos()->delete();

        // Crear nuevos permisos
        foreach ($permisosData as $moduleId => $permisosIds) {
            foreach ($permisosIds as $permisoId) {
                $this->permisos()->create([
                    'module_id' => $moduleId,
                    'permiso_id' => $permisoId
                ]);
            }
        }
    }
}
