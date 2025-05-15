<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradasSalidasGrua extends Model
{
    use HasFactory;

    protected $table = 'entradas_salidas_gruas'; // Especificamos el nombre de la tabla

    protected $fillable = [
        'grua_id',
        'operador_id', // Usas operador_id en lugar de empleado_id
        'fecha_hora_entrada',
        'fecha_hora_salida',
        'destino', // Usas destino en lugar de ubicacion_origen/destino y tipo_movimiento
        'kilometraje_entrada',
        'kilometraje_salida',
        // Confirmado: cliente_id no está aquí
    ];

    protected $casts = [
        'fecha_hora_entrada' => 'datetime',
        'fecha_hora_salida' => 'datetime',
        // Asegúrate de que estos campos existen en tu tabla y que quieres castearlos
    ];

    // --- Relaciones ---

    /**
     * Get the Grua associated with the EntradaSalidaGrua.
     * Obtiene la Grúa asociada con el registro de EntradaSalidaGrua.
     */
    public function grua(): BelongsTo
    {
        return $this->belongsTo(Grua::class);
    }

    /**
     * Get the Empleado (Operador) associated with the EntradaSalidaGrua.
     * Obtiene el Empleado (Operador) asociado con el registro de EntradaSalidaGrua.
     */
    public function operador(): BelongsTo // Usas el nombre de relación 'operador'
    {
        return $this->belongsTo(Empleado::class, 'operador_id'); // Asegúrate de que 'operador_id' es la clave foránea correcta en tu tabla
    }

    // Confirmado: La relación con Cliente no está definida aquí.
}
