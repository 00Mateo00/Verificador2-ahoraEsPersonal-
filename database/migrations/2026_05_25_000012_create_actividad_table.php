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
            $table->string('COD', 50)->nullable(); // ID original de actividad (le es útil a Felipe)
            $table->string('FECHA', 50)->nullable();
            $table->string('FECHA_SAJ', 50)->nullable();
            $table->string('MODALIDAD', 50)->nullable();
            $table->string('TIPO_ACTIVIDAD', 150)->nullable();
            $table->string('SUB_TIPO_ACTIVIDAD', 150)->nullable();
            $table->integer('PARTICIPANTES')->default(0);
            $table->integer('TOTAL_HOMBRES')->default(0);
            $table->integer('TOTAL_MUJERES')->default(0);
            $table->integer('TOTAL_NOBINARIO')->default(0);
            $table->string('FUNCIONARIO', 150)->nullable();
            $table->string('UNIDAD', 150)->nullable();
            $table->string('TIPO_UNIDAD', 50)->nullable();
            $table->string('REGION', 100)->nullable();
            $table->integer('MES')->nullable();
            $table->integer('AÑO')->nullable();
            $table->text('DET_ACTIVIDAD')->nullable();

            // 2. Columnas de Control Interno del MVP (Metadatos) y apoyo del formulario manual
            $table->string('estado', 30)->default('CARGADA'); // CARGADA, NOTIFICADA, PENDIENTE_VERIFICADOR, VERIFICADA
            $table->unsignedBigInteger('carga_id')->nullable(); // Para agrupar las actividades de un mismo lote de Excel
            $table->unsignedBigInteger('usuario_id_asignado')->nullable(); // Funcionario interno asignado
            $table->unsignedBigInteger('unidad_id_asignada')->nullable();   // Unidad interna asignada
            $table->string('ubicacion', 150)->nullable();
            $table->text('observacion')->nullable();

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
