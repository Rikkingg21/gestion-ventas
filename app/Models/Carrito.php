<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carrito extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carritos';

    protected $fillable = [
        'cliente_id',
        'session_id',
        'ip_address',
        'user_agent',
        'estado'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relaciones
     */

    // Relación con cliente (sin foreign key explícita en BD)
    public function cliente()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }

    // Relación con los productos del carrito
    public function productos()
    {
        return $this->hasMany(CarritoProducto::class, 'carrito_id');
    }

    /**
     * Scopes
     */

    // Scope para carritos activos
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope por cliente
    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    // Scope por sesión
    public function scopePorSesion($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    // Scope por IP
    public function scopePorIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Métodos útiles
     */

    // Verificar si el carrito pertenece a un cliente registrado
    public function tieneClienteRegistrado()
    {
        return !is_null($this->cliente_id);
    }

    // Obtener identificador único del carrito
    public function getIdentificadorAttribute()
    {
        if ($this->cliente_id) {
            return 'cliente_' . $this->cliente_id;
        }
        if ($this->session_id) {
            return 'sesion_' . $this->session_id;
        }
        return 'ip_' . $this->ip_address;
    }

    // Calcular total del carrito en USD
    public function getTotalUsdAttribute()
    {
        return $this->productos->sum(function($producto) {
            return $producto->precio_adquirido_usd * $producto->cantidad;
        });
    }

    // Calcular total del carrito en moneda local
    public function getTotalLocalAttribute()
    {
        return $this->productos->sum(function($producto) {
            return $producto->precio_adquirido_local * $producto->cantidad;
        });
    }

    // Obtener cantidad total de productos
    public function getTotalItemsAttribute()
    {
        return $this->productos->sum('cantidad');
    }

    // Vaciar carrito
    public function vaciar()
    {
        return $this->productos()->delete();
    }
}
