<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetodoPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'pais_code',
        'moneda_id',
        'imagen_url',
        'icono_class',
        'detalles',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
