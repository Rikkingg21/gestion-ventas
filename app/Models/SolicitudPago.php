<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

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
        'metodo_pago_id',
        'info_pago',
        'imagen_1',
        'imagen_2',
        'imagen_3',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'info_pago' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relaciones
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

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

    public function estados()
    {
        return $this->hasMany(SolicitudPagoEstado::class, 'solicitud_pago_id');
    }
    public function getImagenUrl($imagenNumero)
    {
        $campo = 'imagen_' . $imagenNumero;
        if ($this->$campo) {
            // Extraer solo el nombre del archivo de la ruta
            $filename = basename($this->$campo);
            return route('admin.solicitudes-pedidos.comprobante', ['hash' => $filename]);
        }
        return null;
    }

    /**
     * Obtener todas las imágenes como array
     */
    public function getImagenes()
    {
        $imagenes = [];
        for ($i = 1; $i <= 3; $i++) {
            $url = $this->getImagenUrl($i);
            if ($url) {
                $imagenes[] = $url;
            }
        }
        return $imagenes;
    }

    /**
     * Accesor para obtener info_pago decodificado automáticamente
     */
    public function getInfoPagoAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
}
