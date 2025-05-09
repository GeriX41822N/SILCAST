<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'empresa',
        'telefono',
        'correo',
        'direccion',
    ];

    public function reportesServicio(): HasMany
    {
        return $this->hasMany(ReporteServicio::class);
    }

    public function gruas(): HasMany
    {
        return $this->hasMany(Grua::class, 'cliente_actual_id');
    }
}