<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    protected $table = 'staff';
    protected $fillable = ['user_id', 'cargo', 'area', 'fecha_contratacion', 'salario', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'fecha_contratacion' => 'date',
        'salario' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
