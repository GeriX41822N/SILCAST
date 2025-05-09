<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grua extends Model
{
    use HasFactory;

    protected $fillable = [
        'unidad',
        'tipo',
        'combustible',
        'capacidad_toneladas',
        'pluma_telescopica_metros',
        'documentacion',
        'operador_id',
        'precio_hora',
        'ayudante_id',
        'cliente_actual_id',
        'estado',
    ];

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'operador_id');
    }

    public function ayudante(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'ayudante_id');
    }

    public function clienteActual(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_actual_id');
    }
}