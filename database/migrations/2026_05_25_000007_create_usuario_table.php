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
        Schema::create('usuario', function (Blueprint $table) {
        $table->id('usuario_id');

        $table->unsignedBigInteger('persona_id')->nullable();

        $table->tinyInteger('usuario_estado_id')->nullable();

        $table->string('usuario_nombre', 30)->nullable();

        $table->string('usuario_pass', 35)->nullable();

        $table->string('usuario_correo', 80)
            ->nullable()
            ->unique();

        $table->string('usuario_rol', 20)
            ->default('usuario');

        $table->foreign('persona_id')
            ->references('persona_id')
            ->on('persona');
    });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
