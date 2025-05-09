<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteServicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'fecha',
        'grua_id',
        'operador_id',
        'ayudante_id',
        'cliente_id',
        'actividad_realizada',
        'hora_inicio',
        'hora_fin',
        'hora_comida',
        'horas_efectivas',
        'ubicacion',
        'archivo_pdf',
        'firma_operador',
        'firma_ayudante',
        'enviado_email',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime', // Puedes usar 'time' si solo quieres la hora
        'hora_fin' => 'datetime',    // Puedes usar 'time' si solo quieres la hora
        'hora_comida' => 'datetime', // Puedes usar 'time' si solo quieres la hora
    ];

    public function grua(): BelongsTo
    {
        return $this->belongsTo(Grua::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'operador_id');
    }

    public function ayudante(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'ayudante_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}