<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'grua_id',
        'precio_unitario',
        'fecha_compra',
        'stock_minimo',
        'ubicacion',
        'tipo',
        'proveedor_id',
        'departamento',
    ];

    public function grua(): BelongsTo
    {
        return $this->belongsTo(Grua::class);
    }

    public function entradas(): HasMany
    {
        return $this->hasMany(EntradaInventario::class);
    }

    public function salidas(): HasMany
    {
        return $this->hasMany(SalidaInventario::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}