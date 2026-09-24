<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 4M-1: Add max_pax to trek_details and tour_details.
     * R19 authorized. Additive only. Nullable.
     */
    public function up(): void
    {
        Schema::table('trek_details', function (Blueprint $table) {
            $table->integer('max_pax')->nullable()->after('difficulty');
        });

        Schema::table('tour_details', function (Blueprint $table) {
            $table->integer('max_pax')->nullable()->after('duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('trek_details', function (Blueprint $table) {
            $table->dropColumn('max_pax');
        });

        Schema::table('tour_details', function (Blueprint $table) {
            $table->dropColumn('max_pax');
        });
    }
};