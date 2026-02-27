<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SolicitudPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_pago';
    protected $fillable = [
        'cliente_id',
        'carrito_id',
        'boleta_id',
        'admin_id',
        'staff_id',
        'monto',
        'metodo_pago',
        'imagen_1',
        'imagen_2',
        'imagen_3',
        'estado'
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
    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'boleta_id');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
