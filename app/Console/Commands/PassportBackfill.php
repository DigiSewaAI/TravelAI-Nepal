<?php

namespace App\Console\Commands;

use App\Models\QrScan;
use App\Models\User;
use App\Models\Waypoint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PassportBackfill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:backfill
                            {--dry-run : Preview changes without applying them to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill waypoint_id for QR scans and generate public IDs for users (Phase 2)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🚀 Starting Passport Backfill...');
        $this->newLine();

        // =============================================
        // TASK 1: Backfill User Public IDs
        // =============================================
        $this->backfillUserPublicIds($dryRun);

        // =============================================
        // TASK 2: Backfill QR Scan Waypoint IDs
        // =============================================
        $this->backfillQrScanWaypoints($dryRun);

        $this->newLine();
        $this->info('✅ Backfill process completed.');

        if ($dryRun) {
            $this->warn('⚠️  Dry-run mode was enabled. No actual changes were saved to the database.');
        }
    }

    /**
     * Generate UUID public IDs for users who don't have one.
     */
    private function backfillUserPublicIds(bool $dryRun): void
    {
        $this->info('📋 Task 1: Generating public IDs for users...');

        $users = User::whereNull('passport_public_id')->get();

        if ($users->isEmpty()) {
            $this->line('   ✅ All users already have public IDs.');
            return;
        }

        $this->line("   Found {$users->count()} users without public ID.");

        if ($dryRun) {
            $this->line('   🔍 [DRY-RUN] Would generate UUIDs for these users:');
            foreach ($users->take(10) as $user) {
                $this->line("      - {$user->id}: {$user->name} ({$user->email}) -> " . Str::uuid()->toString());
            }
            if ($users->count() > 10) {
                $this->line("      ... and " . ($users->count() - 10) . " more.");
            }
            return;
        }

        // Real update
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->passport_public_id = (string) Str::uuid();
            $user->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("   ✅ Updated {$users->count()} users with public IDs.");
    }

    /**
     * Match checkpoint_name to waypoint_id and backfill.
     */
    private function backfillQrScanWaypoints(bool $dryRun): void
    {
        $this->info('📋 Task 2: Matching QR scans to Waypoints...');

        // Only fetch scans that don't have a waypoint_id yet
        $scans = QrScan::whereNull('waypoint_id')->get();

        if ($scans->isEmpty()) {
            $this->line('   ✅ All QR scans already have waypoint IDs.');
            return;
        }

        $this->line("   Found {$scans->count()} QR scans without waypoint_id.");

        $matched = 0;
        $unmatched = 0;
        $ambiguous = 0;
        $log = [];

        if ($dryRun) {
            $this->line('   🔍 [DRY-RUN] Preview of matching results (first 20):');
            $count = 0;
            foreach ($scans as $scan) {
                if ($count >= 20) {
                    $this->line("      ... and " . ($scans->count() - 20) . " more.");
                    break;
                }
                $result = $this->findMatchingWaypoint($scan->checkpoint_name);
                if ($result) {
                    $waypoint = $result['waypoint'];
                    $isAmbiguous = $result['ambiguous'] ?? false;
                    $status = $isAmbiguous ? '⚠️ AMBIGUOUS (using first)' : '✅';
                    $this->line("      {$status} {$scan->checkpoint_name} → {$waypoint->name} (ID: {$waypoint->id})");
                    $matched++;
                } else {
                    $this->line("      ❌ {$scan->checkpoint_name} → UNMATCHED");
                    $unmatched++;
                }
                $count++;
            }
            return;
        }

        // Real update with transaction
        DB::transaction(function () use ($scans, &$matched, &$unmatched, &$ambiguous, &$log) {
            $bar = $this->output->createProgressBar($scans->count());
            $bar->start();

            foreach ($scans as $scan) {
                $result = $this->findMatchingWaypoint($scan->checkpoint_name);

                if ($result) {
                    $waypoint = $result['waypoint'];
                    $isAmbiguous = $result['ambiguous'] ?? false;

                    $scan->waypoint_id = $waypoint->id;
                    $scan->save();
                    $matched++;

                    $logEntry = "✅ Matched: '{$scan->checkpoint_name}' → {$waypoint->name} (ID: {$waypoint->id})";
                    if ($isAmbiguous) {
                        $logEntry .= " [AMBIGUOUS - used first match]";
                    }
                    $log[] = $logEntry;
                } else {
                    // Keep NULL, do not guess
                    $unmatched++;
                    $log[] = "❌ Unmatched: '{$scan->checkpoint_name}' (kept NULL)";
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
        });

        // Show summary
        $this->info("   ✅ Matched: {$matched}");
        $this->warn("   ⚠️  Unmatched: {$unmatched} (kept as NULL)");

        // If there are unmatched scans, write them to a log file for manual review.
        if ($unmatched > 0) {
            $logFile = storage_path('logs/passport_backfill_unmatched.log');
            file_put_contents($logFile, "[" . now() . "] Unmatched QR Scans:\n" . implode("\n", $log) . "\n");
            $this->info("   📄 Full log of unmatched scans saved to: {$logFile}");
        }
    }

    /**
     * Clean checkpoint name by removing extra text like "(Checkpoint 11)".
     */
    private function cleanCheckpointName(string $name): string
    {
        // Remove anything inside parentheses (including the parentheses)
        $cleaned = preg_replace('/\s*\([^)]*\)/', '', $name);
        
        // Remove "Checkpoint" and number patterns if they remain
        $cleaned = preg_replace('/Checkpoint\s*\d+/i', '', $cleaned);
        
        // Trim extra spaces
        return trim($cleaned);
    }

    /**
     * Find a matching waypoint for a given checkpoint name.
     * Returns an array with 'waypoint' and 'ambiguous' flag, or null if no match.
     */
    private function findMatchingWaypoint(string $checkpointName): ?array
    {
        // Step 1: Clean the checkpoint name
        $cleaned = $this->cleanCheckpointName($checkpointName);
        
        // If cleaning removed everything, try original
        if (empty($cleaned)) {
            $cleaned = $checkpointName;
        }
        
        // Step 2: Try exact match on 'name' (case-insensitive)
        $matches = Waypoint::whereRaw('LOWER(name) = ?', [strtolower($cleaned)])->get();
        
        if ($matches->count() === 1) {
            return ['waypoint' => $matches->first(), 'ambiguous' => false];
        }
        
        if ($matches->count() > 1) {
            // Duplicate names found! Pick the first one, mark as ambiguous.
            return ['waypoint' => $matches->first(), 'ambiguous' => true];
        }
        
        // Step 3: Try slug match (slug is unique, so no duplicates)
        $slug = Str::slug($cleaned);
        $waypoint = Waypoint::where('slug', $slug)->first();
        
        if ($waypoint) {
            return ['waypoint' => $waypoint, 'ambiguous' => false];
        }
        
        // Step 4: Try partial match (LIKE '%name%')
        $waypoint = Waypoint::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($cleaned) . '%'])->first();
        
        if ($waypoint) {
            return ['waypoint' => $waypoint, 'ambiguous' => false];
        }
        
        // Step 5: Try partial match on slug as well
        $waypoint = Waypoint::whereRaw('LOWER(slug) LIKE ?', ['%' . strtolower($slug) . '%'])->first();
        
        if ($waypoint) {
            return ['waypoint' => $waypoint, 'ambiguous' => false];
        }
        
        return null; // No match found
    }
}