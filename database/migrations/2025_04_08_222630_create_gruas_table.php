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
        Schema::create('gruas', function (Blueprint $table) {
            $table->id();
            $table->string('unidad')->unique(); // Número económico o placa
            $table->string('tipo');
            $table->string('combustible');
            $table->decimal('capacidad_toneladas', 5, 2);
            $table->decimal('pluma_telescopica_metros', 5, 2)->nullable();
            $table->string('documentacion')->nullable(); // PDF o ruta
            $table->unsignedBigInteger('operador_id')->nullable(); // FK a empleados
            $table->decimal('precio_hora', 8, 2)->nullable();
            $table->unsignedBigInteger('ayudante_id')->nullable(); // FK a empleados
            $table->unsignedBigInteger('cliente_actual_id')->nullable(); // FK a clientes
            $table->string('estado')->default('disponible'); // Campo opcional para el estado

            $table->timestamps();

            $table->foreign('operador_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('ayudante_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('cliente_actual_id')->references('id')->on('clientes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gruas');
    }
};