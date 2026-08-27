<?php

namespace App\Console\Commands;

use App\Models\QrScan;
use App\Services\Passport\CheckinVerificationService;
use Illuminate\Console\Command;

class PassportVerifyScans extends Command
{
    protected $signature = 'passport:verify {--dry-run : Preview changes without applying}';
    protected $description = 'Re-process verification status for existing QR scans (duplicate detection, GPS verification)';

    public function handle(CheckinVerificationService $verificationService)
    {
        $dryRun = $this->option('dry-run');
        $this->info('🔄 Starting verification backfill...');

        $scans = QrScan::whereNull('duplicate_of')
                       ->where('verification_status', 'pending')
                       ->get();

        if ($scans->isEmpty()) {
            $this->line('   No pending scans to process.');
            return;
        }

        $this->line("   Found {$scans->count()} pending scans.");

        $processed = 0;
        $verified = 0;
        $duplicated = 0;

        foreach ($scans as $scan) {
            if ($dryRun) {
                $this->line("   🔍 [DRY-RUN] Scan ID {$scan->id}: {$scan->checkpoint_name}");
                continue;
            }

            // Process the scan as if it's new
            $verificationService->processNewScan($scan);
            $scan->refresh();
            $processed++;

            if ($scan->isVerified()) {
                $verified++;
            } elseif ($scan->isDuplicate()) {
                $duplicated++;
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("   ✅ Processed: {$processed}");
            $this->info("   ✅ Verified by GPS: {$verified}");
            $this->info("   🔁 Marked as duplicates: {$duplicated}");
        } else {
            $this->warn('⚠️  Dry-run mode – no changes applied.');
        }

        $this->info('✅ Verification backfill completed.');
    }
}