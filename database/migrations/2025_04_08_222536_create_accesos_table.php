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
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empleado_id');
            $table->timestamp('entrada')->nullable();
            $table->timestamp('salida')->nullable();
            $table->timestamp('hora_comida_inicio')->nullable(); // Renombrado
            $table->timestamp('hora_comida_fin')->nullable();   // Nuevo campo
            $table->string('ubicacion')->nullable();
            $table->string('dispositivo')->nullable();
            $table->timestamps();
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesos');
    }
};
