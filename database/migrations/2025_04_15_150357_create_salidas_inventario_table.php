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
        Schema::create('salidas_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained()->onDelete('cascade');
            $table->integer('cantidad');
            $table->date('fecha_salida');
            $table->text('descripcion')->nullable();
            $table->string('responsable')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salidas_inventario');
    }
};