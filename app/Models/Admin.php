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
            ->whereHas('modulo', function($query) use ($moduloSlug) {
                $query->where('slug', $moduloSlug);
            })
            ->where('permiso_id', $permisoId)
            ->exists();
    }

    // Obtener todos los permisos del admin agrupados por módulo
    public function getPermisosAgrupados()
    {
        return $this->permisos()
            ->with(['modulo', 'permiso'])
            ->get()
            ->groupBy('modulo.nombre');
    }

    // Asignar permiso a admin
    public function asignarPermiso($moduloId, $permisoId)
    {
        return $this->permisos()->firstOrCreate([
            'modulo_id' => $moduloId,
            'permiso_id' => $permisoId
        ]);
    }

    // Quitar permiso
    public function quitarPermiso($moduloId, $permisoId)
    {
        return $this->permisos()
            ->where('modulo_id', $moduloId)
            ->where('permiso_id', $permisoId)
            ->delete();
    }
}
