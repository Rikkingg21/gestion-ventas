<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SolicitudPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitud_pagos';
    protected $fillable = [
        'cliente_id',
        'carrito_id',
        'pedido_id',
        'admin_id',
        'staff_id',
        'moneda_id',
        'cupon_id',
        'monto',
        'metodo_pago',
        'imagen_1',
        'imagen_2',
        'imagen_3',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'estado' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }
    public function estados()
    {
        return $this->hasMany(SolicitudPagoEstado::class, 'solicitud_pago_id');
    }
}
