<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarritoProducto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carrito_productos';

    protected $fillable = [
        'carrito_id',
        'producto_id',
        'precio_adquirido_usd',
        'precio_adquirido_local',
        'aplica_descuento',
        'porcentaje_descuento',
        'cantidad'
    ];

    protected $casts = [
        'aplica_descuento' => 'boolean',
        'precio_adquirido_usd' => 'decimal:2',
        'precio_adquirido_local' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relaciones
     */
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Accesores
     */
    public function getSubtotalUsdAttribute()
    {
        return $this->precio_adquirido_usd * $this->cantidad;
    }

    public function getSubtotalLocalAttribute()
    {
        return $this->precio_adquirido_local * $this->cantidad;
    }

    public function getPrecioOriginalUsdAttribute()
    {
        if (!$this->aplica_descuento || !$this->porcentaje_descuento) {
            return $this->precio_adquirido_usd;
        }

        return $this->precio_adquirido_usd / (1 - ($this->porcentaje_descuento / 100));
    }

    public function getPrecioOriginalLocalAttribute()
    {
        if (!$this->aplica_descuento || !$this->porcentaje_descuento) {
            return $this->precio_adquirido_local;
        }

        return $this->precio_adquirido_local / (1 - ($this->porcentaje_descuento / 100));
    }

    public function getDescuentoUsdAttribute()
    {
        if (!$this->aplica_descuento || !$this->porcentaje_descuento) {
            return 0;
        }

        $precioOriginal = $this->getPrecioOriginalUsdAttribute();
        return ($precioOriginal - $this->precio_adquirido_usd) * $this->cantidad;
    }

    /**
     * Métodos útiles
     */
    public function actualizarCantidad($nuevaCantidad)
    {
        if ($nuevaCantidad > 0) {
            $this->cantidad = $nuevaCantidad;
            return $this->save();
        }

        // Si cantidad es 0, eliminar el registro usando SoftDeletes
        return $this->delete();
    }

    /**
     * Eliminar permanentemente (override para usar SoftDeletes)
     */
    public function eliminarPermanentemente()
    {
        return $this->forceDelete();
    }

    public function tieneDescuento()
    {
        return $this->aplica_descuento && $this->porcentaje_descuento > 0;
    }
}
