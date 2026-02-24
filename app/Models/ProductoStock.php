<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos_stock';

    protected $fillable = [
        'producto_id',
        'cantidad',
        'stock_minimo',
        'stock_maximo',
        'ubicacion', // opcional: para saber dónde está almacenado
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
    ];

    // Relación inversa con producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Verificar si está bajo stock
    public function stockBajo()
    {
        return $this->cantidad <= $this->stock_minimo;
    }

    // Verificar si hay stock disponible
    public function disponible($cantidad = 1)
    {
        return $this->cantidad >= $cantidad;
    }

    // Reducir stock
    public function reducirStock($cantidad = 1)
    {
        if ($this->disponible($cantidad)) {
            $this->decrement('cantidad', $cantidad);
            return true;
        }
        return false;
    }

    // Aumentar stock
    public function aumentarStock($cantidad = 1)
    {
        $this->increment('cantidad', $cantidad);
    }
}
