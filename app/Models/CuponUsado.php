<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuponUsado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cupones_usados';

    protected $fillable = [
        'cupon_id',
        'cliente_id',
        'producto_id',
        'carrito_id',
        'moneda_id',
        'descuento_obtenido_monto',
        'descuento_obtenido_porcentaje',
        'moneda_id'
    ];

    protected $casts = [
        'descuento_obtenido_monto' => 'decimal:2',
        'descuento_obtenido_porcentaje' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    //relacion con cupon
    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }
    //relacion con cliente
    public function cliente()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }
    //relacion con carrito
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }
    //relacion con moneda
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
