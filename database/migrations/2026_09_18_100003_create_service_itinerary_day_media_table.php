<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_itinerary_day_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('day_id')
                ->constrained('service_itinerary_days')
                ->onDelete('cascade');

            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['day_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_itinerary_day_media');
    }
};