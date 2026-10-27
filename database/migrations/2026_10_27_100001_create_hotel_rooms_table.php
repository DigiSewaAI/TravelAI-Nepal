<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('room_number', 20);
            $table->string('room_type', 50)->default('Deluxe');
            $table->integer('floor')->default(1);
            $table->string('status', 20)->default('available'); // available, occupied, reserved, dirty, maintenance
            $table->timestamps();

            $table->unique(['provider_id', 'room_number']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};