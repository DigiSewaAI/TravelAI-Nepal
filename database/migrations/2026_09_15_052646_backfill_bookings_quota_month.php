<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Support\QuotaPeriod;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('bookings', 'quota_month')) {
            return;
        }

        DB::table('bookings')
            ->whereNull('quota_month')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    if (empty($row->created_at)) {
                        \Log::warning('Booking has null created_at during quota_month backfill', [
                            'booking_id' => $row->id,
                        ]);
                        continue;
                    }

                    $period = QuotaPeriod::forDate($row->created_at);

                    DB::table('bookings')
                        ->where('id', $row->id)
                        ->whereNull('quota_month')
                        ->update(['quota_month' => $period]);
                }
            });
    }

    public function down(): void
    {
        // No-op: do not unset quota_month
    }
};