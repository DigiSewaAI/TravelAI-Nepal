<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_details', function (Blueprint $table) {
            $table->decimal('late_fee_per_day', 10, 2)->default(0)->after('rental_max_days');
            $table->unsignedTinyInteger('damage_deposit_pct')->default(100)->after('late_fee_per_day');
            $table->unsignedTinyInteger('lost_deposit_pct')->default(100)->after('damage_deposit_pct');
        });
    }

    public function down(): void
    {
        Schema::table('rental_details', function (Blueprint $table) {
            $table->dropColumn(['late_fee_per_day', 'damage_deposit_pct', 'lost_deposit_pct']);
        });
    }
};