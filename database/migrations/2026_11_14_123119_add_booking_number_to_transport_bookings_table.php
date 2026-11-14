<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_bookings', 'booking_number')) {
                $table->string('booking_number')->nullable()->after('booking_ref');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transport_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('transport_bookings', 'booking_number')) {
                $table->dropColumn('booking_number');
            }
        });
    }
};