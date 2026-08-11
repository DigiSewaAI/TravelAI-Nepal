<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();

        // 1. Drop foreign keys first (using correct names)
        $this->dropForeignKeyIfExists('sos_alerts', 'sos_alerts_trekker_id_foreign');
        $this->dropForeignKeyIfExists('bookings', 'bookings_trekker_id_foreign');
        $this->dropForeignKeyIfExists('treks', 'treks_agency_id_foreign');
        $this->dropForeignKeyIfExists('treks', 'treks_service_id_foreign');

        // 2. Drop columns
        if (Schema::hasTable('sos_alerts') && Schema::hasColumn('sos_alerts', 'trekker_id')) {
            Schema::table('sos_alerts', function (Blueprint $table) {
                $table->dropColumn('trekker_id');
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'trekker_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('trekker_id');
            });
        }

        if (Schema::hasTable('treks')) {
            Schema::table('treks', function (Blueprint $table) {
                if (Schema::hasColumn('treks', 'agency_id')) {
                    $table->dropColumn('agency_id');
                }
                if (Schema::hasColumn('treks', 'service_id')) {
                    $table->dropColumn('service_id');
                }
            });
        }

        // 3. Drop legacy tables
        Schema::dropIfExists('trekkers');
        Schema::dropIfExists('treks');
        Schema::dropIfExists('agencies');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Safely drop a foreign key if it exists.
     */
    private function dropForeignKeyIfExists(string $table, string $foreignKeyName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        } catch (\Exception $e) {
            // Foreign key may not exist – we can ignore the error
        }
    }

    public function down(): void
    {
        // This migration is one-way. Rollback is not supported.
    }
};