<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->nullable()->constrained('region', 'id')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad');
    }
};
