<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoPrecio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto_precios';

    protected $fillable = [
        'producto_id',
        'moneda_id',
        'precio',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'precio' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
