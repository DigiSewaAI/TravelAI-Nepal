<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('transport_bookings', function (Blueprint $t) {
            if (!Schema::hasColumn('transport_bookings', 'travel_time')) {
                $t->time('travel_time')->nullable()->after('travel_date');
            }
        });
    }
    public function down(): void {
        Schema::table('transport_bookings', function (Blueprint $t) {
            if (Schema::hasColumn('transport_bookings', 'travel_time')) {
                $t->dropColumn('travel_time');
            }
        });
    }
};