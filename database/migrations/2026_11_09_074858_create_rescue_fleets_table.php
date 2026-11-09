<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_fleets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number', 20); // e.g., 9N-AJH
            $table->enum('type', ['helicopter', 'ground_ambulance', 'medical_team']);
            $table->string('model', 100)->nullable(); // H125 Airbus, etc.
            $table->integer('max_altitude_ft')->nullable();
            $table->enum('status', ['available', 'on_mission', 'maintenance', 'offline'])->default('available');
            $table->string('pilot_name', 100)->nullable();
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_fleets');
    }
};