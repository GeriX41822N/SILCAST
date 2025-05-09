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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->nullable()->unique();
            $table->text('descripcion')->nullable();
            $table->integer('cantidad')->default(0);
            $table->string('unidad_medida')->nullable();
            $table->unsignedBigInteger('grua_id')->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->date('fecha_compra')->nullable();
            $table->integer('stock_minimo')->default(0);
            $table->string('ubicacion')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable();
            $table->string('departamento')->nullable();
            $table->timestamps();

            $table->foreign('grua_id')->references('id')->on('gruas')->onDelete('set null');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};