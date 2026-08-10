<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->integer('room_count')->nullable();
            $table->integer('star_rating')->nullable();
            $table->json('amenities')->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->timestamps();

            $table->unique('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_details');
    }
};