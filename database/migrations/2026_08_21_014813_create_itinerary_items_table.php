<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_id')->constrained('itinerary_days')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('time_of_day', ['morning', 'afternoon', 'evening'])->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('currency')->default('NPR');
            $table->enum('pricing_source', ['system_estimate', 'provider_service', 'ai_suggestion'])->default('system_estimate');
            $table->json('pricing_snapshot')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->boolean('is_optional')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('day_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_items');
    }
};