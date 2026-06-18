<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_excel', function (Blueprint $table) {
            $table->id('carga_id');
            $table->unsignedBigInteger('user_id'); // Operador de carga
            $table->string('nombre_archivo', 255);
            $table->integer('total_filas')->default(0);
            $table->string('estado', 50)->default('PROCESADA'); // PROCESADA, CANCELADA
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_excel');
    }
};
