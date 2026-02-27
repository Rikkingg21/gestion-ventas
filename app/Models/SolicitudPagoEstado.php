<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SolicitudPagoEstado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_pago_estados';
    protected $fillable = [
        'solicitud_pago_id',
        'estado',
        'comentarios'
    ];

    protected $casts = [
        'estado' => 'string',
        'comentarios' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con solicitud de pago
    public function solicitudPago()
    {
        return $this->belongsTo(SolicitudPago::class, 'solicitud_pago_id');
    }
}
