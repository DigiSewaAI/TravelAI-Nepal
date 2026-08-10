<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trek_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->integer('duration_days');
            $table->enum('difficulty', ['easy', 'moderate', 'hard']);
            $table->json('itinerary')->nullable();
            $table->integer('max_altitude')->nullable();
            $table->string('season')->nullable(); // e.g., "Spring, Autumn"
            $table->timestamps();

            $table->unique('service_id');
            $table->index('difficulty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trek_details');
    }
};