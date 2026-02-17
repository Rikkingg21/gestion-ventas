<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'route',
        'parent_id',
        'order_position',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con permisos
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    // Relación con módulo padre
    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    // Relación con submódulos
    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id')->orderBy('order_position');
    }

    //Scope para módulos activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    //Scope para módulos principales (sin padre)
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id')->orderBy('order_position');
    }

    //Verificar si el módulo tiene submódulos
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    //Obtener todos los permisos del módulo agrupados por acción
    public function getPermissionsGrouped()
    {
        return $this->permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->slug);
            return end($parts); // Retorna la última parte (create, edit, delete, etc.)
        });
    }
}
