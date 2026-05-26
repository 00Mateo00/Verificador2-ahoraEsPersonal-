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
    Schema::create('comuna', function (Blueprint $table) {
        $table->id('comuna_id');

        $table->unsignedBigInteger('provincia_id')->nullable();

        $table->string('comuna_nombre', 50)->nullable();
        $table->integer('comuna_padre_id')->nullable();
        $table->string('comuna_lat_lng', 50)->nullable();

        $table->foreign('provincia_id')
            ->references('provincia_id')
            ->on('provincia');
    });
}

public function down(): void
{
    Schema::dropIfExists('comuna');
}
};
