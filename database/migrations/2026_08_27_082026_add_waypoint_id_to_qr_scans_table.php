<?php
// database/migrations/2026_08_28_000001_add_waypoint_id_to_qr_scans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_scans', function (Blueprint $table) {
            // ✅ Add waypoint_id (nullable) - अहिलेको लागि केवल यति मात्र
            $table->foreignId('waypoint_id')
                  ->nullable()
                  ->constrained('waypoints')
                  ->nullOnDelete();

            $table->index('waypoint_id');
        });
    }

    public function down(): void
    {
        Schema::table('qr_scans', function (Blueprint $table) {
            $table->dropForeign(['waypoint_id']);
            $table->dropColumn('waypoint_id');
        });
    }
};