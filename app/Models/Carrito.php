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
        'estado',
        'moneda_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relaciones
     */
    public function cliente()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }

    public function productos()
    {
        return $this->hasMany(CarritoProducto::class, 'carrito_id');
    }

    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }

    /**
     * Scopes
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function scopePorSesion($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopePorIp($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Métodos útiles
     */
    public function tieneClienteRegistrado()
    {
        return !is_null($this->cliente_id);
    }

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

    public function getTotalUsdAttribute()
    {
        return $this->productos->sum(function($producto) {
            return $producto->precio_adquirido_usd * $producto->cantidad;
        });
    }

    public function getTotalLocalAttribute()
    {
        return $this->productos->sum(function($producto) {
            return $producto->precio_adquirido_local * $producto->cantidad;
        });
    }

    public function getTotalItemsAttribute()
    {
        return $this->productos->sum('cantidad');
    }

    public function vaciar()
    {
        return $this->productos()->delete();
    }

    public function estaVacio()
    {
        return $this->productos()->count() === 0;
    }
}
