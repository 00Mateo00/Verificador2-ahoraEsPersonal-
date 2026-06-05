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
        Schema::create('region', function (Blueprint $table) {
            $table->id('region_id');

            $table->string('region_nombre', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region');
    }
};
