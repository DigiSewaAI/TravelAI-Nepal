<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // PROVIDER-ITINERARY-09B-04: Composite index for departure capacity queries
            // Query pattern: WHERE departure_id = ? AND status IN (pending,confirmed,completed)
            $table->index(['departure_id', 'status'], 'bookings_departure_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_departure_id_status_index');
        });
    }
};