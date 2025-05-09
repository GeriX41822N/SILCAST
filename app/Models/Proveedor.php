<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;
    // Especifica el nombre correcto de la tabla en la base de datos
    protected $table = 'proveedores';
    
    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'correo',
        'direccion',
        'notas',
    ];

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class);
    }
}