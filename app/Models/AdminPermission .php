<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminPermission extends Model
{
    use SoftDeletes;

    protected $table = 'admin_permissions';

    protected $fillable = [
        'admin_id',
        'permission_id',
        'assigned_by',
        'assigned_at',
        'expires_at',
        'notes'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }

    // Scope para permisos activos (no expirados)
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
