<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Individual
        if (!Schema::hasColumn('individual_bookings', 'booking_number')) {
            Schema::table('individual_bookings', function (Blueprint $table) {
                $table->string('booking_number', 30)->nullable()->unique()->after('id');
            });
            echo "  ✅ Added booking_number to individual_bookings\n";
        }

        // Hotel
        if (!Schema::hasColumn('hotel_bookings', 'booking_number')) {
            Schema::table('hotel_bookings', function (Blueprint $table) {
                $table->string('booking_number', 30)->nullable()->unique()->after('id');
            });
            echo "  ✅ Added booking_number to hotel_bookings\n";
        }

        // ArtCraft
        if (!Schema::hasColumn('custom_orders', 'booking_number')) {
            Schema::table('custom_orders', function (Blueprint $table) {
                $table->string('booking_number', 30)->nullable()->unique()->after('id');
            });
            echo "  ✅ Added booking_number to custom_orders\n";
        }
    }

    public function down(): void
    {
        Schema::table('individual_bookings', fn (Blueprint $t) => $t->dropColumn('booking_number'));
        Schema::table('hotel_bookings', fn (Blueprint $t) => $t->dropColumn('booking_number'));
        Schema::table('custom_orders', fn (Blueprint $t) => $t->dropColumn('booking_number'));
    }
};