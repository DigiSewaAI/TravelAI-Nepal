<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            $table->text('comment')->nullable();
            $table->string('status')->default('pending')->comment('pending, approved, rejected');
            $table->timestamps();

            $table->index(['service_id', 'status']);
            $table->unique(['booking_id', 'user_id']); // एक booking मा एक review मात्र
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};