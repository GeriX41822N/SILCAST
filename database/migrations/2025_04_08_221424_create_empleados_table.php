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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('numero_empleado')->unique();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento');
            $table->string('correo_electronico')->unique();
            $table->string('telefono');
            $table->date('fecha_ingreso');
            $table->string('nss')->nullable();
            $table->string('rfc')->nullable();
            $table->string('curp')->nullable();
            $table->string('calle');
            $table->string('colonia');
            $table->string('cp');
            $table->string('municipio');
            $table->string('clabe')->nullable();
            $table->string('banco')->nullable();
            $table->string('puesto');
            $table->string('area');
            $table->string('turno');
            $table->string('sdr')->nullable();
            $table->string('sdr_imss')->nullable();
            $table->string('estado')->default('activo');
            $table->date('fecha_baja')->nullable();
            $table->string('foto')->nullable()->comment('Ruta o nombre del archivo de la foto del empleado');
            $table->foreignId('supervisor_id')->nullable()->constrained('empleados')->onDelete('set null');

            // --- ¡Añadir la nueva columna estado_civil! ---
            $table->string('estado_civil')->nullable(); // Puedes quitar ->nullable() si es obligatorio
            // ---------------------------------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
