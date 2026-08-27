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
        Schema::create('quotation_requests', function (Blueprint $table) {
    $table->id();
    
    // Traveler (who requested)
    $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
    
    // Provider (who received the request)
    $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
    
    // Reference to the original itinerary (PlannerResult)
    $table->foreignId('planner_result_id')->constrained('planner_results')->onDelete('cascade');
    
    // OR we can store the itinerary data directly as JSON
    $table->json('itinerary_data')->nullable(); // Full itinerary data snapshot
    
    // Traveler's input data (destination, days, budget, etc.)
    $table->json('traveler_input')->nullable();
    
    // Status: pending, viewed, processing, completed, cancelled
    $table->enum('status', ['pending', 'viewed', 'processing', 'completed', 'cancelled'])->default('pending');
    
    // Generated quotation (if completed)
    $table->json('quotation_data')->nullable();
    $table->text('quotation_text')->nullable();
    
    // Timestamps
    $table->timestamps();
    
    // Indexes
    $table->index(['provider_id', 'status']);
    $table->index('traveler_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_requests');
    }
};
