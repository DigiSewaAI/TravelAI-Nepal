<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('individual_details')) {
            Schema::create('individual_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('provider_id')->unique()->constrained('providers')->cascadeOnDelete();
                $table->text('bio')->nullable();
                $table->integer('experience_years')->nullable();
                $table->json('languages')->nullable();
                $table->json('specialties')->nullable();
                $table->json('service_areas')->nullable();
                $table->string('avatar_path')->nullable();
                $table->string('cover_image_path')->nullable();
                $table->decimal('price_per_day', 10, 2)->nullable();
                $table->string('currency', 3)->default('NPR');
                $table->boolean('is_published')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_details');
    }
};