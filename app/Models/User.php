<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'email',
        'nro_documento',
        'tipo_documento',
        'telefono',
        'password'
    ];

    /**
     * Los atributos que deben estar ocultos
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos
     */
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
    public function canDo($permisoId, $moduloSlug = null)
    {
        // Super admin tiene todos los permisos
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Verificar permisos de admin
        if ($this->isAdmin()) {
            if ($moduloSlug) {
                return $this->admin->tienePermiso($moduloSlug, $permisoId);
            }

            // Si no se especifica módulo, verificar en cualquier módulo
            return $this->admin->permisos()
                ->where('permiso_id', $permisoId)
                ->exists();
        }

        // Verificar permisos de staff
        if ($this->staff) {
            if ($moduloSlug) {
                return $this->staff->tienePermiso($moduloSlug, $permisoId);
            }

            return $this->staff->permisos()
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
    public function canRead($moduloSlug)
    {
        return $this->canDo(2, $moduloSlug);
    }

    // Verificar si tiene permiso para actualizar (permiso_id = 3)
    public function canUpdate($moduloSlug)
    {
        return $this->canDo(3, $moduloSlug);
    }

    // Verificar si tiene permiso para eliminar (permiso_id = 4)
    public function canDelete($moduloSlug)
    {
        return $this->canDo(4, $moduloSlug);
    }

    // Obtener todos los permisos del usuario
    public function getAllPermissions()
    {
        if ($this->isSuperAdmin()) {
            // Si es super admin, retornar todos los permisos existentes
            return Permiso::all();
        }

        $permisos = collect();

        if ($this->isAdmin()) {
            $permisos = $permisos->merge(
                $this->admin->permisos()->with('permiso')->get()->pluck('permiso')
            );
        }

        if ($this->staff) {
            $permisos = $permisos->merge(
                $this->staff->permisos()->with('permiso')->get()->pluck('permiso')
            );
        }

        return $permisos->unique('id');
    }
}
