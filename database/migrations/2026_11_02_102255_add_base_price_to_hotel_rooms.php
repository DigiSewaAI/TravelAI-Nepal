<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('hotel_rooms', 'base_price')) return;
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->decimal('base_price', 10, 2)->nullable()->after('room_type');
        });
    }
    public function down(): void {
        if (Schema::hasColumn('hotel_rooms', 'base_price')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->dropColumn('base_price');
            });
        }
    }
};
