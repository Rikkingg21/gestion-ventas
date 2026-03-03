<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cupones';

    protected $fillable = [
        'moneda_id',
        'nombre',
        'codigo',
        'porcentaje_descuento',
        'descuento_monto',
        'fecha_inicio',
        'fecha_fin',
        'stok_inicial',
        'stok_actual',
        'is_active',
    ];

    protected $casts = [
        'porcentaje_descuento' => 'decimal:2',
        'descuento_monto' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Validar si el cupón es válido para usar
    public function esValido()
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->fecha_fin && $this->fecha_fin->isPast()) {
            return false;
        }
        if ($this->stok_actual <= 0) {
            return false;
        }
        return true;
    }
    //relacion con moneda
    public function moneda()
    {
        return $this->belongsTo(Moneda::class, 'moneda_id');
    }
}
