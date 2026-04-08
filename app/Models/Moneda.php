<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Moneda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'monedas';

    protected $fillable = [
        'pais',
        'pais_code',
        'nombre',
        'codigo_iso',
        'simbolo',
        'tasa_cambio_usd', // tasa de cambio respecto al USD
        'is_active',
        'aceptar_pagos'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'aceptar_pagos' => 'boolean',
        'tasa_cambio_usd' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
}
