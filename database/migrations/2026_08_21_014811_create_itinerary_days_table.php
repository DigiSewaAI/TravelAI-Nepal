<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('planner_results')->onDelete('cascade');
            $table->integer('day_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('overnight_waypoint_id')->nullable()->constrained('waypoints')->onDelete('set null');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('estimated_time_hours', 5, 1)->nullable();
            $table->integer('altitude_m')->nullable();
            $table->timestamps();

            $table->unique(['result_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_days');
    }
};