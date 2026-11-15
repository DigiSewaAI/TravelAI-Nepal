<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'trip_id')) {
                $table->foreignId('trip_id')->nullable()->after('id')
                    ->constrained('transport_trips')->onDelete('set null');
            }
            if (!Schema::hasColumn('transport_bookings', 'passenger_count')) {
                $table->integer('passenger_count')->default(1)->after('passenger_email');
            }
            if (!Schema::hasColumn('transport_bookings', 'booking_type')) {
                $table->enum('booking_type', ['per_seat', 'full_vehicle'])->default('per_seat')->after('passenger_count');
            }
            if (!Schema::hasColumn('transport_bookings', 'pickup_point')) {
                $table->string('pickup_point', 255)->nullable()->after('booking_type');
            }
            if (!Schema::hasColumn('transport_bookings', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 2)->nullable()->after('pickup_point');
            }
            if (!Schema::hasColumn('transport_bookings', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('price_per_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'trip_id')) {
                $table->dropForeign(['trip_id']);
                $table->dropColumn('trip_id');
            }
            foreach (['passenger_count', 'booking_type', 'pickup_point', 'price_per_unit', 'total_amount'] as $col) {
                if (Schema::hasColumn('transport_bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};