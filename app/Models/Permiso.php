<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permiso extends Model
{
    use SoftDeletes;

    protected $table = 'permisos';

    protected $fillable = [
        'nombre'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con módulos a través de admin_permisos
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'admin_permisos', 'permiso_id', 'modulo_id')
            ->withPivot('admin_id')
            ->withTimestamps();
    }

    // Relación con admin_permisos
    public function adminPermisos()
    {
        return $this->hasMany(AdminPermiso::class);
    }

    // Relación con staff_permisos
    public function staffPermisos()
    {
        return $this->hasMany(StaffPermiso::class);
    }

    // Scope para buscar por nombre
    public function scopeSearch($query, $search)
    {
        return $query->where('nombre', 'like', "%{$search}%");
    }
}
