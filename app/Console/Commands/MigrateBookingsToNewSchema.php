<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Trekker;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class MigrateBookingsToNewSchema extends Command
{
    protected $signature = 'migrate:bookings {--dry-run : Simulate without making changes}';
    protected $description = 'Migrate existing bookings to use traveler_id and service_id';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Get bookings that are not yet migrated (where traveler_id is null)
        $bookings = Booking::whereNull('traveler_id')->get();
        $this->info("Found {$bookings->count()} bookings to migrate.");

        if ($dryRun) {
            $this->info('DRY RUN – no changes will be made.');
        }

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($bookings as $booking) {
            // Get the trekker
            $trekker = $booking->trekker;
            if (!$trekker) {
                $this->warn("Booking #{$booking->id} has no trekker. Skipping.");
                $skipped++;
                continue;
            }

            // Get the trek and its service
            $trek = $booking->trek;
            if (!$trek) {
                $this->warn("Booking #{$booking->id} has no trek. Skipping.");
                $skipped++;
                continue;
            }

            $service = $trek->service;
            if (!$service) {
                $this->warn("Trek #{$trek->id} has no service_id. Skipping booking #{$booking->id}.");
                $skipped++;
                continue;
            }

            DB::beginTransaction();

            try {
                // Find or create user from trekker
                $user = User::where('email', $trekker->email)->first();

                if (!$user) {
                    // Create a traveler user
                    $user = User::create([
                        'name' => $trekker->name,
                        'email' => $trekker->email,
                        'password' => bcrypt(\Illuminate\Support\Str::random(32)), // random password
                        'role' => 'traveler',
                        'phone' => $trekker->phone,
                        'avatar' => null,
                    ]);
                    $this->line("Created new traveler user #{$user->id} for email {$trekker->email}");
                } else {
                    // Ensure role is traveler (if not already)
                    if ($user->role !== 'traveler') {
                        $this->warn("User #{$user->id} exists but role is '{$user->role}'. Updating to 'traveler'.");
                        if (!$dryRun) {
                            $user->role = 'traveler';
                            $user->save();
                        }
                    }
                }

                // Update the booking
                if (!$dryRun) {
                    $booking->traveler_id = $user->id;
                    $booking->service_id = $service->id;
                    $booking->save();
                }

                $migrated++;
                $this->line("Migrated booking #{$booking->id}: traveler_id={$user->id}, service_id={$service->id}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to migrate booking #{$booking->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("Migration complete. Migrated: {$migrated}, Skipped: {$skipped}, Errors: {$errors}");

        return 0;
    }
}