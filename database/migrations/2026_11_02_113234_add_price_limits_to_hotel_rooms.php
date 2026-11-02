<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('hotel_rooms', function (Blueprint $t) {
            if (!Schema::hasColumn('hotel_rooms','min_price')) $t->decimal('min_price',10,2)->nullable()->after('base_price');
            if (!Schema::hasColumn('hotel_rooms','max_price')) $t->decimal('max_price',10,2)->nullable()->after('min_price');
        });
    }
    public function down(): void {
        Schema::table('hotel_rooms', function (Blueprint $t) {
            if (Schema::hasColumn('hotel_rooms','min_price')) $t->dropColumn('min_price');
            if (Schema::hasColumn('hotel_rooms','max_price')) $t->dropColumn('max_price');
        });
    }
};