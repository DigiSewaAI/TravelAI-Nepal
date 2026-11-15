<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('transport_vehicles')->onDelete('restrict');
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('restrict');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->decimal('price_per_seat', 10, 2)->default(0);
            $table->decimal('price_per_vehicle', 10, 2)->nullable();
            $table->integer('seats_total')->default(0);
            $table->integer('seats_available')->default(0);
            $table->string('driver_name', 100)->nullable();
            $table->string('pickup_point', 255)->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->index(['provider_id', 'departure_date', 'status']);
            $table->index(['vehicle_id', 'departure_date']);
            $table->index(['route_id', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_trips');
    }
};