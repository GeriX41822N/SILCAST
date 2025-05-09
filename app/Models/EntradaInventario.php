<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaInventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventario_id',
        'cantidad',
        'fecha_entrada',
        'descripcion',
        'responsable',
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
    ];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }
}