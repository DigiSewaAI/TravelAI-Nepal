<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art_craft_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('craft_type')->nullable();
            $table->string('materials')->nullable();
            $table->string('artist_name')->nullable();
            $table->string('dimensions')->nullable();
            $table->boolean('is_handmade')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('art_craft_details');
    }
};