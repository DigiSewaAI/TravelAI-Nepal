<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waypoints', function (Blueprint $table) {
            // ✅ Default false — safer
            // Genuine overnight waypoints ले explicit रूपमा true राख्नुपर्छ (Phase 4B मा seeder मार्फत)
            $table->boolean('is_overnight_stop')->default(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('waypoints', function (Blueprint $table) {
            $table->boolean('is_overnight_stop')->default(true)->change();
        });
    }
};