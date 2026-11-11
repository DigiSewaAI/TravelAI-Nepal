<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rescue_providers', function (Blueprint $table) {
            // Add base coordinates for distance calculation
            $table->decimal('base_lat', 10, 7)->nullable()->after('coverage_radius_km');
            $table->decimal('base_lng', 10, 7)->nullable()->after('base_lat');
        });
    }

    public function down(): void
    {
        Schema::table('rescue_providers', function (Blueprint $table) {
            $table->dropColumn(['base_lat', 'base_lng']);
        });
    }
};