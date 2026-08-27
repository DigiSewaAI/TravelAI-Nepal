<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\Passport\QrSecurityService;
use Illuminate\Console\Command;

class PassportRegenerateQrTokens extends Command
{
    protected $signature = 'passport:regenerate-tokens {--dry-run : Preview changes without applying}';
    protected $description = 'Regenerate secure QR tokens for all bookings (or only those without tokens)';

    public function handle(QrSecurityService $qrSecurityService)
    {
        $dryRun = $this->option('dry-run');
        $this->info('🔐 Regenerating QR tokens for bookings...');

        $bookings = Booking::whereNull('qr_token')->orWhere('qr_token', '')->get();

        if ($bookings->isEmpty()) {
            $this->line('   ✅ All bookings already have QR tokens.');
            return;
        }

        $this->line("   Found {$bookings->count()} bookings without tokens.");

        if ($dryRun) {
            $this->line('   🔍 [DRY-RUN] Would generate tokens for:');
            foreach ($bookings->take(10) as $booking) {
                $this->line("      - Booking #{$booking->id} (traveler: {$booking->traveler_id})");
            }
            if ($bookings->count() > 10) {
                $this->line("      ... and " . ($bookings->count() - 10) . " more.");
            }
            return;
        }

        $bar = $this->output->createProgressBar($bookings->count());
        $bar->start();

        foreach ($bookings as $booking) {
            $booking->regenerateQrToken();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("   ✅ Regenerated tokens for {$bookings->count()} bookings.");
    }
}