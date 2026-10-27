<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('vehicle_number', 50);
            $table->string('vehicle_type', 50); // Bus, Jeep, Car, Van, Hiace, Bike
            $table->integer('capacity')->default(1);
            $table->string('driver_name', 100)->nullable();
            $table->string('route_name', 150)->nullable(); // e.g. KTM → Pokhara
            $table->string('status', 20)->default('available'); // available, on-route, maintenance, charging
            $table->timestamps();

            $table->unique(['provider_id', 'vehicle_number']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_vehicles');
    }
};