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
        Schema::create('entradas_salidas_gruas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grua_id')->constrained()->onDelete('cascade');
            $table->foreignId('operador_id')->nullable()->constrained('empleados')->onDelete('set null');
            $table->timestamp('fecha_hora_entrada')->nullable();
            $table->timestamp('fecha_hora_salida')->nullable();
            $table->string('destino')->nullable();
            $table->decimal('kilometraje_entrada', 8, 2)->nullable();
            $table->decimal('kilometraje_salida', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entradas_salidas_gruas');
    }
};