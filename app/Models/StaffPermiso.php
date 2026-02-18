<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPermiso extends Model
{
    protected $table = 'staff_permisos';

    protected $fillable = ['staff_id', 'module_id', 'permiso_id', 'assigned_by'];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
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
