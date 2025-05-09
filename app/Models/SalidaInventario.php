<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalidaInventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventario_id',
        'cantidad',
        'fecha_salida',
        'descripcion',
        'responsable',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
    ];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }
}