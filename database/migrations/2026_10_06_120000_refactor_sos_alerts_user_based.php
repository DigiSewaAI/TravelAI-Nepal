<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refactor sos_alerts to support User-based SOS (Option 2).
     * - Keep legacy trekker_id/booking_id (nullable now)
     * - Add traveler_id (auth user), provider_id (recipient)
     * - Add status lifecycle + timestamps
     */
    public function up(): void
    {
        Schema::table('sos_alerts', function (Blueprint $table) {
            if (!Schema::hasColumn('sos_alerts', 'traveler_id')) {
                $table->foreignId('traveler_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('sos_alerts', 'provider_id')) {
                $table->foreignId('provider_id')
                    ->nullable()
                    ->after('traveler_id')
                    ->constrained('providers')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('sos_alerts', 'status')) {
                $table->string('status', 20)
                    ->default('pending')
                    ->after('message')
                    ->index();
            }

            if (!Schema::hasColumn('sos_alerts', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }

            if (!Schema::hasColumn('sos_alerts', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('sent_at');
            }
        });

        // Make legacy columns nullable (raw SQL — safest across MySQL versions)
        // Wrapped in try/catch: if column or FK missing, silently continue.
        try {
            DB::statement('ALTER TABLE sos_alerts MODIFY trekker_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // Legacy column may already be nullable or missing — ignore
        }

        try {
            DB::statement('ALTER TABLE sos_alerts MODIFY booking_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // Legacy column may already be nullable or missing — ignore
        }
    }

    public function down(): void
    {
        Schema::table('sos_alerts', function (Blueprint $table) {
            foreach (['traveler_id', 'provider_id'] as $col) {
                if (Schema::hasColumn('sos_alerts', $col)) {
                    try { $table->dropForeign([$col]); } catch (\Throwable $e) {}
                    $table->dropColumn($col);
                }
            }

            foreach (['status', 'sent_at', 'resolved_at'] as $col) {
                if (Schema::hasColumn('sos_alerts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};