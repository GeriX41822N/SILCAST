<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // <-- Importa HasOne


class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_empleado',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'correo_electronico',
        'telefono',
        'fecha_ingreso',
        'nss',
        'rfc',
        'curp',
        'calle',
        'colonia',
        'cp',
        'municipio',
        'clabe',
        'banco',
        'puesto',
        'area',
        'turno',
        'sdr',
        'sdr_imss',
        'estado',
        'fecha_baja',
        'foto',
        'supervisor_id',
        'estado_civil', // <-- Asegúrate que estado_civil está aquí
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_baja' => 'date',
    ];

    // Relaciones existentes (mantener)
    public function rol(): BelongsTo
    {
        // Nota: Si solo usas Spatie, esta relación 'rol()' puede ser redundante o confusa.
        // La asignación de roles se hace a través del modelo Usuario y Spatie.
        // Si no la usas, puedes considerarla para eliminarla en el futuro.
        return $this->belongsTo(Rol::class);
    }

    // --- ¡CORREGIR LA RELACIÓN CON USUARIO! ---
    // Cambiamos de hasMany a hasOne y de 'usuarios' a 'usuario' (singular)
    public function usuario(): HasOne // <-- Cambiar a HasOne y nombre singular
    {
        // Asegúrate que la clave foránea en la tabla 'usuarios' es 'empleado_id'
        return $this->hasOne(Usuario::class, 'empleado_id');
    }
    // ------------------------------------------


    public function gruasOperadas(): HasMany
    {
        return $this->hasMany(Grua::class, 'operador_id');
    }

    public function gruasAyudadas(): HasMany
    {
        return $this->hasMany(Grua::class, 'ayudante_id');
    }

    public function reportesServicioOperados(): HasMany
    {
        return $this->hasMany(ReporteServicio::class, 'operador_id');
    }

    public function reportesServicioAyudados(): HasMany
    {
        return $this->hasMany(ReporteServicio::class, 'ayudante_id');
    }

    public function accesos(): HasMany
    {
        return $this->hasMany(Acceso::class);
    }

    public function entradasSalidasGruas(): HasMany
    {
        return $this->hasMany(EntradasSalidasGrua::class, 'operador_id');
    }
    // Relación con el supervisor (un empleado puede tener un supervisor)
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'supervisor_id');
    }

    // Relación con los empleados que supervisa (un empleado puede supervisar a varios empleados)
    public function subordinados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'supervisor_id');
    }
}
