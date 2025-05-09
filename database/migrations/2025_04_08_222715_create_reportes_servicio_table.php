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
        Schema::create('reportes_servicio', function (Blueprint $table) {
            $table->id();
            $table->integer('folio')->unsigned()->unique();
            $table->unsignedBigInteger('grua_id');
            $table->unsignedBigInteger('operador_id'); // quien la operó
            $table->unsignedBigInteger('ayudante_id')->nullable(); // quien ayudó (opcional)
            $table->unsignedBigInteger('cliente_id')->nullable(); // Cliente registrado (opcional)
            $table->string('nombre_cliente')->nullable(); // Nombre del cliente si no está registrado
            $table->string('nombre_empresa_cliente')->nullable(); // Empresa del cliente si no está registrado
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->boolean('tomo_descanso')->default(false); // Indica si se tomó una hora de descanso
            $table->string('descripcion_servicio');
            $table->string('ubicacion');
            $table->decimal('duracion_horas', 5, 2)->nullable(); // Podría ser calculado
            $table->string('archivo_pdf')->nullable(); // reporte generado
            $table->string('firma_operador')->nullable(); // Ruta o referencia de la firma
            $table->string('firma_ayudante')->nullable(); // Ruta o referencia de la firma
            $table->string('correo_cliente')->nullable(); // Correo para enviar el reporte
            $table->string('telefono_cliente')->nullable(); // Teléfono para enviar el reporte
            $table->timestamps();

            $table->foreign('grua_id')->references('id')->on('gruas')->onDelete('cascade');
            $table->foreign('operador_id')->references('id')->on('empleados')->onDelete('cascade');
            $table->foreign('ayudante_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null'); // Permitir null si el cliente no está registrado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_servicio');
    }
};