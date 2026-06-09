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
        Schema::create('actividad', function (Blueprint $table) {
            $table->id('actividad_id');

            // 1. Columnas de Origen Directo (Nombres limpios unificados)
            $table->string('COD', 50)->unique(); // ID original de actividad (le es útil a Felipe)
            $table->string('UNIDAD', 150); // existe en modelo
            $table->string('REGION', 100); // existe en modelo
            $table->integer('MES'); // existe en modelo
            $table->integer('AÑO'); // existe en modelo
            $table->string('FECHA_SAJ', 50); // existe en modelo

            // deben venir remapeadas
            $table->string('MODALIDAD', 50); // existe en modelo
            $table->string('TIPO_ACTIVIDAD', 150); // existe en modelo
            $table->string('SUB_TIPO_ACTIVIDAD', 150); // existe en modelo

            // opcionales:
            $table->string('FECHA', 50)->nullable(); // existe en modelo
            $table->integer('PARTICIPANTES')->nullable(); // existe en modelo
            $table->integer('TOTAL_HOMBRES')->nullable(); // existe en modelo
            $table->integer('TOTAL_MUJERES')->nullable(); // existe en modelo
            $table->integer('TOTAL_NOBINARIO')->nullable(); // existe en modelo
            $table->text('DET_ACTIVIDAD')->nullable(); // existe en modelo
            $table->string('FUNCIONARIO', 150)->nullable(); // existe en modelo

            // 2. Columnas de Control Interno del MVP (Metadatos) y apoyo del formulario manual
            $table->string('estado', 30)->default('CARGADA'); // CARGADA, NOTIFICADA, PENDIENTE_VERIFICADOR, VERIFICADA
            $table->unsignedBigInteger('carga_id'); // Para agrupar las actividades de un mismo lote de Excel
            $table->unsignedBigInteger('usuario_id_asignado'); // Funcionario interno asignado
            $table->unsignedBigInteger('unidad_id_asignada');   // Unidad interna asignada

            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Llaves foráneas con las tablas de infraestructura existentes

            $table->foreign('unidad_id_asignada')
                ->references('unidad_id')
                ->on('unidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad');
    }
};
