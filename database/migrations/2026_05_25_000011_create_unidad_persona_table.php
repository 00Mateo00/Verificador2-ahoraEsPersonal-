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
    Schema::create('unidad_persona', function (Blueprint $table) {
        $table->id('up_id');

        $table->unsignedBigInteger('persona_id');

        $table->unsignedBigInteger('unidad_id');

        $table->unsignedBigInteger('jefe_id');

        $table->foreign('persona_id')
            ->references('persona_id')
            ->on('persona');

        $table->foreign('unidad_id')
            ->references('unidad_id')
            ->on('unidad');

        $table->foreign('jefe_id')
            ->references('persona_id')
            ->on('persona');
    });
}

public function down(): void
{
    Schema::dropIfExists('unidad_persona');
}
};
