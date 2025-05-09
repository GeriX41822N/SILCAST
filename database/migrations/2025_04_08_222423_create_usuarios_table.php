<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            // Columna empleado_id: Clave foránea que referencia la tabla empleados
            // Asegúrate de que esta columna sea unsignedBigInteger para coincidir con el tipo de ID de Laravel
            $table->unsignedBigInteger('empleado_id'); // Si es obligatorio que un usuario tenga un empleado
            // Si un usuario pudiera existir sin empleado, podrías hacerlo nullable():
            // $table->unsignedBigInteger('empleado_id')->nullable();

            // Eliminamos la columna rol_id y su clave foránea, ya que Spatie maneja la asignación de roles en model_has_roles
            // $table->unsignedBigInteger('rol_id'); // <-- Eliminar esta línea


            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            // Definición de claves foráneas

            // Clave foránea a la tabla 'empleados'
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');

            // Eliminamos la clave foránea a la tabla 'roles', ya que rol_id fue eliminado
            // $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade'); // <-- Eliminar esta línea
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
