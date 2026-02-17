<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use SoftDeletes;

    protected $table = 'admins';
    protected $fillable = ['user_id', 'nivel', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSuperAdmin()
    {
        return $this->nivel === 'super_admin';
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
