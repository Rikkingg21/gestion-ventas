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
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_permissions')
                    ->withTimestamps();
    }
    public function isSuperAdmin()
    {
        return $this->nivel === 'super_admin';
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    //Verificar si tiene un permiso específico
    public function hasPermission($permissionSlug)
    {
        if ($this->isSuperAdmin()) {
            return true; // Super admin tiene todos los permisos
        }

        return $this->permissions()
                    ->where('slug', $permissionSlug)
                    ->exists();
    }
    //Verificar si tiene algún permiso de un módulo
    public function hasAnyPermission($moduleSlug, $actions = [])
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissions()
                    ->whereHas('module', function ($q) use ($moduleSlug) {
                        $q->where('slug', $moduleSlug);
                    })
                    ->when(!empty($actions), function ($q) use ($actions) {
                        foreach ($actions as $action) {
                            $q->orWhere('slug', 'LIKE', "%.{$action}");
                        }
                    })
                    ->exists();
    }
    //Obtener todos los permisos agrupados por módulo
    public function getPermissionsGrouped()
    {
        return $this->permissions()
                    ->with('module')
                    ->get()
                    ->groupBy('module.name');
    }
}
