<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');

            $table->enum('transport_type', ['bus', 'jeep', 'car', 'van', 'flight', 'heli']);
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->time('departure_time')->nullable();
            $table->integer('duration_minutes')->nullable();

            $table->decimal('price_per_person', 10, 2)->default(0);
            $table->decimal('price_per_vehicle', 10, 2)->nullable();
            $table->integer('total_seats')->default(1);

            $table->boolean('ac_available')->default(false);
            $table->enum('private_shared', ['private', 'shared'])->default('private');
            $table->boolean('driver_included')->default(true);
            $table->boolean('fuel_included')->default(true);

            $table->enum('booking_type', ['instant', 'on-request'])->default('on-request');
            $table->text('cancellation_policy')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique('service_id');
            $table->index('transport_type');
            $table->index(['from_location_id', 'to_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_details');
    }
};