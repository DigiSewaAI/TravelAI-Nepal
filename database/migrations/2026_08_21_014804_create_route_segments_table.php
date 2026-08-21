<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_waypoint_id')->constrained('waypoints')->onDelete('cascade');
            $table->foreignId('to_waypoint_id')->constrained('waypoints')->onDelete('cascade');
            $table->integer('sequence')->unsigned();
            $table->decimal('distance_km', 8, 2);
            $table->decimal('estimated_time_hours', 5, 1)->nullable();
            $table->integer('elevation_gain_m')->nullable();
            $table->integer('elevation_loss_m')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // MySQL compatible unique (application will enforce active uniqueness)
            $table->unique(['route_id', 'sequence', 'deleted_at']);
            $table->index(['route_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_segments');
    }
};