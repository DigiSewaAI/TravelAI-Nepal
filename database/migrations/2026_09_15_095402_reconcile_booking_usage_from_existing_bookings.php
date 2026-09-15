<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\QuotaPeriod;

return new class extends Migration
{
    /**
     * One-time reconciliation: populate booking_usage from existing
     * current-period quota-consuming bookings.
     *
     * Idempotent: existing usage rows are preserved (not overwritten).
     * Counts only pending/confirmed/completed in current NPT month.
     */
    public function up(): void
    {
        if (!Schema::hasTable('booking_usage') || !Schema::hasTable('bookings')) {
            return;
        }

        $currentMonth = QuotaPeriod::current();

        $counts = DB::table('bookings')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->where('bookings.quota_month', $currentMonth)
            ->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])
            ->select('services.provider_id', DB::raw('COUNT(*) as total'))
            ->groupBy('services.provider_id')
            ->get();

        foreach ($counts as $row) {
            $exists = DB::table('booking_usage')
                ->where('provider_id', $row->provider_id)
                ->where('month', $currentMonth)
                ->exists();

            if (!$exists) {
                DB::table('booking_usage')->insert([
                    'provider_id' => $row->provider_id,
                    'month'       => $currentMonth,
                    'count'       => (int) $row->total,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op — preserve usage data
    }
};