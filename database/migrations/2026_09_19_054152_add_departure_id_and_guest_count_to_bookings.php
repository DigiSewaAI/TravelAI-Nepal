<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('departure_id')
                  ->nullable()
                  ->after('service_id')
                  ->constrained('departures')
                  ->onDelete('set null');

            $table->unsignedInteger('guest_count')
                  ->default(1)
                  ->after('departure_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['departure_id']);
            $table->dropColumn(['departure_id', 'guest_count']);
        });
    }
};