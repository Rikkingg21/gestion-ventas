<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'email',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'cliente_id');
    }
    public function carritoActivo()
    {
        return $this->carritos()->where('estado', 'activo')->first();
    }
}
