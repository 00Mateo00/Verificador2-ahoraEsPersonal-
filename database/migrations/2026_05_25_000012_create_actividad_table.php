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

        $table->unsignedBigInteger('usuario_id');

        $table->string('region', 100);

        $table->string('tipo_unidad', 100);

        $table->string('unidad_operativa', 150);

        $table->string('tipo', 100);

        $table->string('nombre_actividad', 100);

        $table->string('objetivo', 200);

        $table->integer('n_participantes');

        $table->string('ubicacion', 150);

        $table->string('observacion', 200)->nullable();

        // reemplaza fecha_actividad
        $table->timestamp('created_at')->useCurrent();

        $table->timestamp('updated_at')->nullable();

        $table->boolean('activo')->default(true);

        $table->foreign('usuario_id')
            ->references('usuario_id')
            ->on('usuario');
    });
}

public function down(): void
{
    Schema::dropIfExists('actividad');
}
};
