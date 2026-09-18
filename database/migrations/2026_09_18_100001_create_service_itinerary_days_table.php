<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_itinerary_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');

            $table->integer('day_number');
            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('start_waypoint_id')
                ->nullable()
                ->constrained('waypoints')
                ->onDelete('set null');

            $table->foreignId('end_waypoint_id')
                ->nullable()
                ->constrained('waypoints')
                ->onDelete('set null');

            $table->foreignId('overnight_waypoint_id')
                ->nullable()
                ->constrained('waypoints')
                ->onDelete('set null');

            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('estimated_time_hours', 5, 1)->nullable();
            $table->integer('elevation_gain_m')->nullable();
            $table->integer('elevation_loss_m')->nullable();
            $table->integer('altitude_m')->nullable();
            $table->json('meals_included')->nullable();
            $table->string('accommodation')->nullable();

            $table->timestamps();

            $table->unique(['service_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_itinerary_days');
    }
};