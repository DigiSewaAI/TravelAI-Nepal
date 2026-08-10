<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Trek;
use App\Models\Service;
use App\Models\TrekDetail;
use App\Models\TourDetail;
use App\Models\HotelDetail;
use App\Models\ServiceCategory;
use App\Models\Provider;
use App\Models\Agency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateTreksToServices extends Command
{
    protected $signature = 'migrate:treks {--dry-run : Simulate without making changes}';
    protected $description = 'Migrate existing treks to services and details';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Ensure service categories exist
        $categories = ServiceCategory::pluck('id', 'slug');
        if ($categories->isEmpty()) {
            $this->error('Service categories not seeded. Please run ServiceCategorySeeder first.');
            return 1;
        }

        $treks = Trek::all();
        $this->info("Found {$treks->count()} treks to migrate.");

        if ($dryRun) {
            $this->info('DRY RUN – no changes will be made.');
        }

        $migrated = 0;
        $skipped = 0;

        foreach ($treks as $trek) {
            // Skip if already has service_id
            if ($trek->service_id) {
                $this->warn("Trek #{$trek->id} already has service_id {$trek->service_id}. Skipping.");
                $skipped++;
                continue;
            }

            // Get the agency and its provider
            $agency = $trek->agency;
            if (!$agency) {
                $this->warn("Trek #{$trek->id} has no agency. Skipping.");
                $skipped++;
                continue;
            }

            $user = $agency->user;
            if (!$user) {
                $this->warn("Agency #{$agency->id} has no user. Skipping.");
                $skipped++;
                continue;
            }

            $provider = $user->providers()->first();
            if (!$provider) {
                $this->warn("User #{$user->id} has no provider. Skipping.");
                $skipped++;
                continue;
            }

            // Determine service category
            $categorySlug = $trek->category; // 'trek', 'tour', 'hotel'
            $categoryId = $categories[$categorySlug] ?? null;
            if (!$categoryId) {
                $this->warn("Unknown category '{$categorySlug}' for trek #{$trek->id}. Skipping.");
                $skipped++;
                continue;
            }

            DB::beginTransaction();

            try {
                // Create Service
                $service = Service::create([
                    'provider_id' => $provider->id,
                    'service_category_id' => $categoryId,
                    'name' => $trek->name,
                    'slug' => Str::slug($trek->name) . '-' . $trek->id,
                    'description' => null,
                    'price' => $trek->price,
                    'currency' => 'NPR',
                    'cover_image' => $trek->cover_image,
                    'gallery' => $trek->gallery,
                    'status' => 'active',
                    'location_id' => null, // we'll set later if needed
                ]);

                // Create category-specific details
                $detailData = [
                    'service_id' => $service->id,
                ];

                if ($categorySlug === 'trek') {
                    $detailData['duration_days'] = $trek->duration_days;
                    $detailData['difficulty'] = $trek->difficulty;
                    $detailData['itinerary'] = $trek->itinerary;
                    $detailData['max_altitude'] = null;
                    $detailData['season'] = null;
                    TrekDetail::create($detailData);
                } elseif ($categorySlug === 'tour') {
                    $detailData['duration_days'] = $trek->duration_days;
                    $detailData['itinerary'] = $trek->itinerary;
                    $detailData['inclusions'] = null;
                    $detailData['exclusions'] = null;
                    TourDetail::create($detailData);
                } elseif ($categorySlug === 'hotel') {
                    $detailData['room_count'] = null;
                    $detailData['star_rating'] = null;
                    $detailData['amenities'] = null;
                    $detailData['check_in_time'] = null;
                    $detailData['check_out_time'] = null;
                    HotelDetail::create($detailData);
                }

                // Update trek with service_id
                if (!$dryRun) {
                    $trek->service_id = $service->id;
                    $trek->save();
                }

                $migrated++;
                $this->line("Migrated trek #{$trek->id} to service #{$service->id}");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to migrate trek #{$trek->id}: " . $e->getMessage());
            }
        }

        $this->info("Migration complete. Migrated: {$migrated}, Skipped: {$skipped}");
        return 0;
    }
}