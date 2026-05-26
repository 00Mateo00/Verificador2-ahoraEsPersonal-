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
    Schema::create('unidad', function (Blueprint $table) {
        $table->id('unidad_id');

        $table->unsignedBigInteger('comuna_id')->nullable();

        $table->unsignedTinyInteger('unidad_tipo_id')->nullable();

        $table->unsignedBigInteger('usuario_id_modificacion')->nullable();

        $table->string('unidad_nombre', 50)->nullable();
        $table->string('unidad_direccion', 255)->nullable();
        $table->string('unidad_correo', 100)->nullable();
        $table->string('unidad_horario', 255)->nullable();
        $table->string('unidad_jefe', 120)->nullable();
        $table->string('unidad_fono', 255)->nullable();

        $table->integer('unidad_monto_arriendo')->nullable();

        $table->date('unidad_inicio_contrato')->nullable();
        $table->date('unidad_termino_contrato')->nullable();

        $table->smallInteger('unidad_superficie_construida')->nullable();

        $table->tinyInteger('unidad_n_oficinas')->nullable();

        $table->tinyInteger('un_id')->nullable();

        $table->tinyInteger('unidad_oculta_publico')->nullable();

        $table->tinyInteger('hide')->nullable();

        $table->unsignedBigInteger('unidad_padre_id')->nullable();

        $table->tinyInteger('consultorio')->nullable();

        $table->date('fecha_modificacion')->nullable();

        $table->tinyInteger('p_id')->nullable();

        $table->time('inicio_colacion')->nullable();
        $table->time('termino_colacion')->nullable();

        $table->text('observacion_general')->nullable();
        $table->text('observacion_servicios')->nullable();
        $table->text('observacion_horarios')->nullable();
        $table->text('observacion_cargos')->nullable();

        $table->tinyInteger('piloto')->nullable();

        $table->string('unidad_contacto_inmueble', 100)->nullable();

        $table->string('unidad_propietario', 80)->nullable();

        $table->string('abreviacion', 10)->nullable();

        $table->string('estadistica', 10)->nullable();

        $table->string('latitud', 20)->nullable();

        $table->string('longitud', 20)->nullable();

        $table->foreign('comuna_id')
            ->references('comuna_id')
            ->on('comuna');

        $table->foreign('unidad_tipo_id')
            ->references('unidad_tipo_id')
            ->on('unidad_tipo');

        $table->foreign('usuario_id_modificacion')
            ->references('usuario_id')
            ->on('usuario');

        $table->foreign('unidad_padre_id')
            ->references('unidad_id')
            ->on('unidad');
    });
}

public function down(): void
{
    Schema::dropIfExists('unidad');
}
};
