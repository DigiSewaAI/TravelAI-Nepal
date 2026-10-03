<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Provider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillBookingNumbers extends Command
{
    protected $signature = 'bookings:backfill-numbers';
    protected $description = 'Backfill booking_number for existing bookings (BOOKING-REFERENCE-SYSTEM-01)';

    public function handle(): int
    {
        $this->info('Starting backfill...');

        // Step 1: Ensure all providers have a code
        $providersWithoutCode = Provider::whereNull('code')->get();
        if ($providersWithoutCode->count() > 0) {
            $this->info("Generating codes for {$providersWithoutCode->count()} providers...");
            foreach ($providersWithoutCode as $provider) {
                $provider->code = Provider::generateUniqueCode($provider->name);
                $provider->saveQuietly();
                $this->line("  Provider #{$provider->id} '{$provider->name}' → {$provider->code}");
            }
        }

        // Step 2: Backfill booking numbers chronologically
        $bookings = Booking::with('service.provider')
            ->whereNull('booking_number')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings to backfill.');
            return 0;
        }

        $this->info("Backfilling {$bookings->count()} bookings...");

        $counters = []; // ["CODE-BK-YY-" => count]

        DB::transaction(function () use ($bookings, &$counters) {
            foreach ($bookings as $booking) {
                $provider = $booking->service?->provider;
                $code = $provider?->code ?? 'SYS';
                $year = $booking->created_at->format('y');
                $prefix = "{$code}-BK-{$year}-";

                $counters[$prefix] = ($counters[$prefix] ?? 0) + 1;
                $seq = str_pad((string) $counters[$prefix], 5, '0', STR_PAD_LEFT);

                $booking->booking_number = $prefix . $seq;
                $booking->saveQuietly();

                $this->line("  Booking #{$booking->id} → {$booking->booking_number}");
            }
        });

        $this->info('Backfill complete!');
        return 0;
    }
}