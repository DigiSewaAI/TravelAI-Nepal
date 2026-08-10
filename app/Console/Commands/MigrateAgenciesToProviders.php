<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agency;
use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MigrateAgenciesToProviders extends Command
{
    protected $signature = 'migrate:agencies {--dry-run : Simulate without making changes}';
    protected $description = 'Migrate existing agencies to users and providers';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Fetch the default provider type (Trekking Agency)
        $defaultType = ProviderType::where('slug', 'trekking-agency')->first();
        if (!$defaultType) {
            $this->error('Default provider type "trekking-agency" not found. Please run ProviderTypeSeeder first.');
            return 1;
        }

        $agencies = Agency::all();
        $this->info("Found {$agencies->count()} agencies to migrate.");

        if ($dryRun) {
            $this->info('DRY RUN – no changes will be made.');
        }

        $migrated = 0;
        $skipped = 0;

        foreach ($agencies as $agency) {
            // Skip if agency already has a user_id
            if ($agency->user_id) {
                $this->warn("Agency #{$agency->id} already has user_id {$agency->user_id}. Skipping.");
                $skipped++;
                continue;
            }

            // Check if a user with this email already exists
            $existingUser = User::where('email', $agency->email)->first();
            if ($existingUser) {
                $this->warn("User with email {$agency->email} already exists. Skipping agency #{$agency->id}.");
                $skipped++;
                continue;
            }

            DB::beginTransaction();

            try {
                // Create User
                $user = User::create([
                    'name' => $agency->name,
                    'email' => $agency->email,
                    'password' => $agency->password, // copy existing hashed password
                    'role' => 'provider_owner',
                    'phone' => $agency->phone,
                    'avatar' => $agency->logo_url ?? null,
                ]);

                // Create Provider
                $provider = Provider::create([
                    'user_id' => $user->id,
                    'name' => $agency->name,
                    'slug' => Str::slug($agency->name) . '-' . $agency->id, // unique slug
                    'description' => null,
                    'logo_url' => $agency->logo_url,
                    'contact_email' => $agency->email,
                    'contact_phone' => $agency->phone,
                    'address' => $agency->address,
                    'verification_status' => 'verified', // trusted existing agencies
                    'is_active' => true,
                ]);

                // Attach default provider type
                $provider->types()->attach($defaultType->id);

                // Update agency with user_id
                if (!$dryRun) {
                    $agency->user_id = $user->id;
                    $agency->save();
                }

                $migrated++;
                $this->line("Migrated agency #{$agency->id} (email: {$agency->email}) to user #{$user->id} and provider #{$provider->id}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to migrate agency #{$agency->id}: " . $e->getMessage());
                if (!$dryRun) {
                    // Optionally continue or stop
                }
            }
        }

        $this->info("Migration complete. Migrated: {$migrated}, Skipped: {$skipped}");

        return 0;
    }
}