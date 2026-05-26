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
    Schema::create('archivos', function (Blueprint $table) {
        $table->id('archivo_id');

        $table->unsignedBigInteger('actividad_id');

        $table->string('archivo_nombre', 255);

        $table->string('archivo_ruta', 255);

        $table->string('archivo_tipo', 50);

        $table->integer('archivo_size');

        // reemplaza fecha_subida
        $table->timestamp('created_at')->useCurrent();

        $table->timestamp('updated_at')->nullable();

        $table->foreign('actividad_id')
            ->references('actividad_id')
            ->on('actividad')
            ->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::dropIfExists('archivos');
}
};
