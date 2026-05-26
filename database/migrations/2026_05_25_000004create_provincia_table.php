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
    Schema::create('provincia', function (Blueprint $table) {
        $table->id('provincia_id');

        $table->unsignedBigInteger('region_id')->nullable();

        $table->string('provincia_nombre', 50)->nullable();
        $table->string('provincia_lat_lng', 50)->nullable();

        $table->foreign('region_id')
            ->references('region_id')
            ->on('region');
    });
}

public function down(): void
{
    Schema::dropIfExists('provincia');
}
};
