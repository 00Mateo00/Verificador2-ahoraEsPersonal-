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
            $table->string('UNIDAD', 150);
            $table->string('REGION', 100);
            $table->integer('MES');
            $table->integer('AÑO');
            $table->date('FECHA_SAJ');

            // deben venir remapeadas
            $table->string('MODALIDAD', 50);
            $table->string('TIPO_ACTIVIDAD', 150);
            $table->string('SUB_TIPO_ACTIVIDAD', 150);

            // opcionales:
            $table->date('FECHA')->nullable();
            $table->integer('PARTICIPANTES')->nullable();
            $table->integer('TOTAL_HOMBRES')->nullable();
            $table->integer('TOTAL_MUJERES')->nullable();
            $table->integer('TOTAL_NOBINARIO')->nullable();
            $table->text('DET_ACTIVIDAD')->nullable();
            $table->string('FUNCIONARIO', 150)->nullable();

            // 2. Columnas de Control Interno del MVP (Metadatos) y apoyo del formulario manual
            $table->string('estado', 30)->default('CARGADA');
            $table->unsignedBigInteger('carga_id'); // Para agrupar las actividades de un mismo lote de Excel
            $table->unsignedBigInteger('unidad_id_asignada');

            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Llaves foráneas con las tablas de infraestructura existentes

            $table->foreign('unidad_id_asignada')
                ->references('id')
                ->on('unidad');

            // indexar filtros de busqueda
            $table->index('FECHA');
            $table->index('unidad_id_asignada');
            $table->index('estado');
            $table->index('UNIDAD');
            $table->index('REGION');

            // Índice Fulltext defensivo para optimizar búsquedas textuales de texto libre en motores compatibles (MySQL/PostgreSQL)
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText('DET_ACTIVIDAD');
            }
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
