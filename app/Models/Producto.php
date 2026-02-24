<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precioUSD',
        'precioLocal',
        'aplica_descuento',
        'tipo_producto', // 'fisico' o 'digital'
        'url_recurso', // para productos digitales (link a Drive, etc.)
        'sku', // código único para productos físicos
        'imagen_url_1',
        'imagen_url_2',
        'imagen_url_3',
        'imagen_url_4',
        'imagen_url_5',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'aplica_descuento' => 'boolean',
        'precioUSD' => 'decimal:2',
        'precioLocal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Relación con stock (para productos físicos)
    public function stock()
    {
        return $this->hasOne(ProductoStock::class);
    }

    // Verificar si es producto digital
    public function esDigital()
    {
        return $this->tipo_producto === 'digital';
    }

    // Verificar si es producto físico
    public function esFisico()
    {
        return $this->tipo_producto === 'fisico';
    }

    // Obtener stock actual (si es físico)
    public function getStockActualAttribute()
    {
        if ($this->esFisico() && $this->stock) {
            return $this->stock->cantidad;
        }
        return null;
    }

    // Scope para productos activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope por tipo
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo_producto', $tipo);
    }
}
