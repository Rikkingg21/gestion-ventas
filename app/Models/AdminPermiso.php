<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermiso extends Model
{
    protected $table = 'admin_permisos';

    protected $fillable = ['admin_id', 'module_id', 'permiso_id', 'assigned_by'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permiso()
    {
        return $this->belongsTo(Permiso::class);
    }
    public function assignedBy()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
