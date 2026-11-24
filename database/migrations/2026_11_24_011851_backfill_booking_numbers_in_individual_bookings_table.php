<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill NULL booking_number (safe — कोई existing value overwrite नै)
        $nullRows = DB::table('individual_bookings')
            ->whereNull('booking_number')
            ->orderBy('id')
            ->get(['id', 'created_at']);

        $counters = [];
        foreach ($nullRows as $b) {
            $year = $b->created_at ? date('Y', strtotime($b->created_at)) : date('Y');
            if (!isset($counters[$year])) {
                $counters[$year] = DB::table('individual_bookings')
                    ->where('booking_number', 'like', 'IND-' . $year . '-%')
                    ->count();
            }
            $counters[$year]++;
            $newNumber = 'IND-' . $year . '-' . str_pad($counters[$year], 4, '0', STR_PAD_LEFT);

            // Skip if collision (safety)
            $exists = DB::table('individual_bookings')->where('booking_number', $newNumber)->exists();
            if ($exists) {
                continue;
            }

            DB::table('individual_bookings')
                ->where('id', $b->id)
                ->whereNull('booking_number') // concurrency safety
                ->update(['booking_number' => $newNumber]);
        }
    }

    public function down(): void
    {
        // No-op — backfill is non-destructive, no rollback needed
    }
};