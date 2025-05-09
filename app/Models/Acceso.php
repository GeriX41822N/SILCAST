<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acceso extends Model
{
    use HasFactory;

    protected $fillable = [
        'empleado_id',
        'entrada',
        'salida',
        'hora_comida_inicio',
        'hora_comida_fin',
        'ubicacion',
        'dispositivo',
    ];

    protected $casts = [
        'entrada' => 'datetime',
        'salida' => 'datetime',
        'hora_comida_inicio' => 'datetime',
        'hora_comida_fin' => 'datetime',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
} 