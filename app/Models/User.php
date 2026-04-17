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
        'apellido_materno',
        'pais', //CODIGO ISO
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
    public function canDo($permisoId, $moduleIdentifier = null)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Si es admin
        if ($this->isAdmin()) {
            $query = $this->admin->permisos();

            if ($moduleIdentifier) {
                // Si es numérico, buscar por ID
                if (is_numeric($moduleIdentifier)) {
                    $query->where('module_id', $moduleIdentifier);
                } else {
                    // Si es string (slug), buscar el módulo por slug
                    $module = Module::where('slug', $moduleIdentifier)->first();
                    if ($module) {
                        $query->where('module_id', $module->id);
                    } else {
                        return false;
                    }
                }
            }

            return $query->where('permiso_id', $permisoId)->exists();
        }

        // Si es staff
        if ($this->staff) {
            $query = $this->staff->permisos();

            if ($moduleIdentifier) {
                if (is_numeric($moduleIdentifier)) {
                    $query->where('module_id', $moduleIdentifier);
                } else {
                    $module = Module::where('slug', $moduleIdentifier)->first();
                    if ($module) {
                        $query->where('module_id', $module->id);
                    } else {
                        return false;
                    }
                }
            }

            return $query->where('permiso_id', $permisoId)->exists();
        }

        return false;
    }
    public function hasPermission($moduleIdentifier, $permisoId)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $moduleId = null;

        // Convertir slug a ID si es necesario
        if (!is_numeric($moduleIdentifier)) {
            $module = Module::where('slug', $moduleIdentifier)->first();
            if (!$module) {
                return false;
            }
            $moduleId = $module->id;
        } else {
            $moduleId = $moduleIdentifier;
        }

        // Si es admin
        if ($this->isAdmin()) {
            return $this->admin->permisos()
                ->where('module_id', $moduleId)
                ->where('permiso_id', $permisoId)
                ->exists();
        }

        // Si es staff
        if ($this->staff) {
            return $this->staff->permisos()
                ->where('module_id', $moduleId)
                ->where('permiso_id', $permisoId)
                ->exists();
        }

        return false;
    }

    // Actualiza los métodos
    public function canCreate($moduleIdentifier)
    {
        return $this->canDo(1, $moduleIdentifier);
    }

    public function canRead($moduleIdentifier)
    {
        return $this->hasPermission($moduleIdentifier, 2);
    }

    public function canUpdate($moduleIdentifier)
    {
        return $this->hasPermission($moduleIdentifier, 3);
    }

    public function canDelete($moduleIdentifier)
    {
        return $this->hasPermission($moduleIdentifier, 4);
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
