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
        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient', 255);
            $table->string('subject', 255);
            $table->string('mailable_class', 255);
            $table->json('payload');
            $table->text('error_message')->nullable();
            $table->string('status', 30)->default('PENDING');
            $table->integer('attempts')->default(1);
            $table->timestamps();

            $table->index('status');
            $table->index('recipient');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_mails');
    }
};