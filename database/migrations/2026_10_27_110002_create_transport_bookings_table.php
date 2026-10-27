<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('transport_vehicles')->nullOnDelete();
            $table->string('passenger_name', 100);
            $table->string('passenger_email', 150)->nullable();
            $table->string('booking_ref', 50)->unique();
            $table->string('route_name', 150);
            $table->date('travel_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending, confirmed, cancelled
            $table->timestamps();

            $table->index(['provider_id', 'created_at']);
            $table->index(['provider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_bookings');
    }
};