<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_missions', function (Blueprint $table) {
            $table->id();
            $table->string('mission_number', 30)->unique(); // RSC-2026-0001
            $table->foreignId('sos_alert_id')->constrained('sos_alerts')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rescue_fleet_id')->nullable()->constrained('rescue_fleets')->nullOnDelete();
            
            $table->enum('status', ['dispatched', 'accepted', 'en_route', 'on_scene', 'extracting', 'completed', 'cancelled'])->default('dispatched');
            
            // Location
            $table->decimal('pickup_lat', 10, 8);
            $table->decimal('pickup_lng', 11, 8);
            $table->decimal('dropoff_lat', 10, 8)->nullable();
            $table->decimal('dropoff_lng', 11, 8)->nullable();
            
            // Financials
            $table->decimal('cost_estimate', 12, 2)->nullable();
            $table->decimal('final_cost', 12, 2)->nullable();
            
            // Timestamps
            $table->timestamp('dispatched_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('en_route_at')->nullable();
            $table->timestamp('on_scene_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_missions');
    }
};