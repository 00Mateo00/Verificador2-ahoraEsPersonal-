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
    Schema::create('persona', function (Blueprint $table) {
        $table->id('persona_id');

        $table->string('persona_rut', 12)->nullable();
        $table->string('persona_nombre', 50)->nullable();
        $table->string('persona_apellido', 50)->nullable();

        $table->tinyInteger('persona_funcionario')->nullable();
    });
}

public function down(): void
{
    Schema::dropIfExists('persona');
}
};
