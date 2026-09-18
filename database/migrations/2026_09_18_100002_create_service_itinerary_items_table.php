<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_itinerary_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('day_id')
                ->constrained('service_itinerary_days')
                ->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('time_of_day', ['morning', 'afternoon', 'evening'])->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_optional')->default(false);
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['day_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_itinerary_items');
    }
};