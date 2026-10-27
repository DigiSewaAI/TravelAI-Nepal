<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('hotel_rooms')->nullOnDelete();
            $table->string('task_name', 100); // Clean Room, Make Bed, etc.
            $table->string('assigned_to', 100)->nullable();
            $table->string('status', 20)->default('queue'); // queue, in_progress, done
            $table->timestamps();

            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_tasks');
    }
};