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
            $table->string('unidad_nombre', 50)->nullable();
            $table->string('unidad_correo', 100)->nullable();
            $table->foreignId('region_id')->nullable()->constrained('region', 'region_id')->nullOnDelete();
            /* $table->string('unidad_jefe', 120)->nullable(); */
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad');
    }
};
