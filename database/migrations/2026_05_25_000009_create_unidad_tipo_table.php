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
    Schema::create('unidad_tipo', function (Blueprint $table) {
        $table->tinyIncrements('unidad_tipo_id');

        $table->string('unidad_tipo_desc', 80)->nullable();
        $table->string('nombre_corto', 15)->nullable();
    });
}

public function down(): void
{
    Schema::dropIfExists('unidad_tipo');
}
};
