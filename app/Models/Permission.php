<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permissions';

    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con módulo
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Relación con administradores
    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_permissions')
                    ->withTimestamps();
    }

    // Relación con staff
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_permissions')
                    ->withTimestamps();
    }

    // Scope para permisos de un módulo específico
    public function scopeByModule($query, $moduleSlug)
    {
        return $query->whereHas('module', function ($q) use ($moduleSlug) {
            $q->where('slug', $moduleSlug);
        });
    }

    // Verificar si es un permiso de lectura

    public function isReadPermission()
    {
        return str_contains($this->slug, '.read') ||
               str_contains($this->slug, '.view') ||
               str_contains($this->slug, '.index');
    }

    // Verificar si es un permiso de escritura
    public function isWritePermission()
    {
        return str_contains($this->slug, '.create') ||
               str_contains($this->slug, '.store') ||
               str_contains($this->slug, '.update') ||
               str_contains($this->slug, '.edit');
    }

    // Verificar si es un permiso de eliminación
    public function isDeletePermission()
    {
        return str_contains($this->slug, '.delete') ||
               str_contains($this->slug, '.destroy');
    }
}
