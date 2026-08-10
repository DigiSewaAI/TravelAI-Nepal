<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add new columns (nullable initially)
            $table->foreignId('traveler_id')
                  ->nullable()
                  ->after('trekker_id')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('service_id')
                  ->nullable()
                  ->after('trek_id')
                  ->constrained('services')
                  ->nullOnDelete();

            // Add indexes for performance
            $table->index('traveler_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['traveler_id']);
            $table->dropForeign(['service_id']);
            $table->dropColumn(['traveler_id', 'service_id']);
        });
    }
};