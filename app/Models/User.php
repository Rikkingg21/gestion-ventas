<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'username',
        'nombres',
        'apellido_paterno',
        'pais',
        'apellido_materno',
        'email',
        'nro_documento',
        'tipo_documento',
        'telefono',
        'password'
    ];

    // Los atributos que deben estar ocultos
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Los atributos que deben ser convertidos
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Relación con cliente
    public function client()
    {
        return $this->hasOne(Client::class);
    }
    // Relacion con Admin
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }
    public function isAdmin()
    {
        return $this->admin !== null;
    }

    public function isSuperAdmin()
    {
        return $this->admin && $this->admin->isSuperAdmin();
    }
    // Relacion con Staff
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }

    // Verificar si el usuario tiene un permiso específico
    public function canDo($permisoId, $moduleId = null)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Si es admin
        if ($this->isAdmin()) {
            $query = $this->admin->permisos();
            if ($moduleId) {
                $query->where('module_id', $moduleId);
            }
            return $query->where('permiso_id', $permisoId)->exists();
        }

        // Si es staff
        if ($this->staff) {
            $query = $this->staff->permisos();
            if ($moduleId) {
                $query->where('module_id', $moduleId);
            }
            return $query->where('permiso_id', $permisoId)->exists();
        }

        return false;
    }
    public function hasPermission($moduleId, $permisoId)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Si es admin (y estamos en contexto de admin)
        if ($this->isAdmin()) {
            return $this->admin->permisos()
                ->where('module_id', $moduleId)
                ->where('permiso_id', $permisoId)
                ->exists();
        }

        // Si es staff (y estamos en contexto de staff)
        if ($this->staff) {
            return $this->staff->permisos()
                ->where('module_id', $moduleId)
                ->where('permiso_id', $permisoId)
                ->exists();
        }

        return false;
    }

    // Verificar si tiene permiso para crear (permiso_id = 1)
    public function canCreate($moduloSlug)
    {
        return $this->canDo(1, $moduloSlug);
    }

    // Verificar si tiene permiso para leer (permiso_id = 2)
    public function canRead($moduleId)
    {
        return $this->hasPermission($moduleId, 2);
    }

    // Verificar si tiene permiso para actualizar (permiso_id = 3)
    public function canUpdate($moduleId)
    {
        return $this->hasPermission($moduleId, 3);
    }

    // Verificar si tiene permiso para eliminar (permiso_id = 4)
    public function canDelete($moduleId)
    {
        return $this->hasPermission($moduleId, 4);
    }

    // Verificar si tiene permiso para un conjunto de acciones en un módulo
    public function hasAnyPermission($moduleId, array $permisosIds)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $this->admin->permisos()
                ->where('module_id', $moduleId)
                ->whereIn('permiso_id', $permisosIds)
                ->exists();
        }

        if ($this->staff) {
            return $this->staff->permisos()
                ->where('module_id', $moduleId)
                ->whereIn('permiso_id', $permisosIds)
                ->exists();
        }

        return false;
    }
    // Verificar si tiene todos los permisos especificados en un módulo
    public function hasAllPermissions($moduleId, array $permisosIds)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $userPermisos = [];

        if ($this->isAdmin()) {
            $userPermisos = $this->admin->permisos()
                ->where('module_id', $moduleId)
                ->whereIn('permiso_id', $permisosIds)
                ->pluck('permiso_id')
                ->toArray();
        } elseif ($this->staff) {
            $userPermisos = $this->staff->permisos()
                ->where('module_id', $moduleId)
                ->whereIn('permiso_id', $permisosIds)
                ->pluck('permiso_id')
                ->toArray();
        }

        return count(array_intersect($permisosIds, $userPermisos)) === count($permisosIds);
    }
}
