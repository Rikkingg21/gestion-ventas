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
    public function tienePermiso($moduloSlug, $permisoId)
    {
        return $this->permisos()
            ->whereHas('module', function($query) use ($moduloSlug) {
                $query->where('slug', $moduloSlug);
            })
            ->where('permiso_id', $permisoId)
            ->exists();
    }

    // Obtener todos los permisos del admin agrupados por módulo
    public function getPermisosAgrupados()
    {
        return $this->permisos()
            ->with(['module', 'permiso'])
            ->get()
            ->groupBy('modulo.nombre');
    }

    // Asignar permiso a admin
    public function asignarPermiso($moduleId, $permisoId)
    {
        return $this->permisos()->firstOrCreate([
            'module_id' => $moduleId,
            'permiso_id' => $permisoId
        ]);
    }

    // Quitar permiso
    public function quitarPermiso($moduleId, $permisoId)
    {
        return $this->permisos()
            ->where('module_id', $moduleId)
            ->where('permiso_id', $permisoId)
            ->delete();
    }
}
