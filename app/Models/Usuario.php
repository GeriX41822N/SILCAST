<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles; // Asegúrate que esté aquí

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles; // Asegúrate que esté aquí

    protected $fillable = [
        'empleado_id',
        'rol_id',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
   // protected $appends = ['roles', 'permissions']; // <-- ¡Añadimos esta línea!


    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    // No necesitas la relación 'rol()' personalizada si te basas en Spatie.
    // El Trait HasRoles ya proporciona la relación 'roles()' y 'permissions()'.
}