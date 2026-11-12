<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\ProviderType;
use App\Models\ServiceCategory;

return new class extends Migration
{
    /**
     * Data cleanup migration — Idempotent
     * 
     * 1. Migrate Pemba's service (individual → guide)
     * 2. Seed pivot table (provider_type ↔ service_category)
     * 3. Detach guide from trekking/tour agency
     * 4. Delete orphan categories (individual, trek, rescue)
     * 
     * Safe to run multiple times.
     */
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════
        // STEP 1: Migrate Pemba's service (individual 10 → guide 45)
        // ═══════════════════════════════════════════════════════════
        $individualCat = ServiceCategory::where('slug', 'individual')->first();
        $guideCat = ServiceCategory::where('slug', 'guide')->first();

        if ($individualCat && $guideCat) {
            $migrated = DB::table('services')
                ->where('service_category_id', $individualCat->id)
                ->update(['service_category_id' => $guideCat->id]);
            // migrated = number of services moved (0 if already moved)
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 2: Seed pivot table (provider_type ↔ service_category)
        // Using syncWithoutDetaching = idempotent
        // ═══════════════════════════════════════════════════════════
        $mappings = [
            'trekking-agency'    => ['trekking', 'tour', 'activity', 'experience'],
            'tour-agency'        => ['tour', 'trekking', 'activity', 'experience'],
            'activity-provider'  => ['activity', 'experience'],
            'local-experience'   => ['experience', 'activity'],
            'hotel'              => ['hotel'],
            'resort'             => ['resort'],
            'lodge'              => ['lodge'],
            'homestay'           => ['homestay'],
            'restaurant'         => ['restaurant'],
            'transport-provider' => ['transport'],
            'money-changer'      => ['money-changer'],
            'art-craft'          => ['art-craft'],
        ];

        foreach ($mappings as $ptSlug => $catSlugs) {
            $pt = ProviderType::where('slug', $ptSlug)->first();
            if (!$pt) continue;

            $catIds = ServiceCategory::whereIn('slug', $catSlugs)->pluck('id')->toArray();
            if (empty($catIds)) continue;

            $pt->serviceCategories()->syncWithoutDetaching($catIds);
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 3: Detach guide from trekking-agency & tour-agency
        // (Guide = Individual Vertical, not Service Category for Trekking)
        // ═══════════════════════════════════════════════════════════
        $guideCat = ServiceCategory::where('slug', 'guide')->first();
        if ($guideCat) {
            DB::table('provider_type_service_category')
                ->where('service_category_id', $guideCat->id)
                ->whereIn('provider_type_id', function ($q) {
                    $q->select('id')->from('provider_types')
                      ->whereIn('slug', ['trekking-agency', 'tour-agency']);
                })
                ->delete();
        }

        // ═══════════════════════════════════════════════════════════
        // STEP 4: Delete orphan categories (only if no services)
        // ═══════════════════════════════════════════════════════════
        $orphanSlugs = ['individual', 'trek', 'rescue'];
        foreach ($orphanSlugs as $slug) {
            $cat = ServiceCategory::where('slug', $slug)->first();
            if (!$cat) continue;

            // Safety: Only delete if no services use this category
            $serviceCount = DB::table('services')
                ->where('service_category_id', $cat->id)
                ->count();

            if ($serviceCount === 0) {
                // Detach from pivot first
                DB::table('provider_type_service_category')
                    ->where('service_category_id', $cat->id)
                    ->delete();
                // Then delete category
                $cat->delete();
            }
            // If serviceCount > 0: skip (protect data)
        }
    }

    /**
     * Idempotent rollback — No destructive operations.
     * 
     * Categories 10, 43, 48 may have been deleted; cannot be re-created
     * without original data. If rollback needed, restore from backup SQL.
     */
    public function down(): void
    {
        // Pivot seed rollback — detach only known seeded rows
        $mappings = [
            'trekking-agency'    => ['trekking', 'tour', 'activity', 'experience'],
            'tour-agency'        => ['tour', 'trekking', 'activity', 'experience'],
            'activity-provider'  => ['activity', 'experience'],
            'local-experience'   => ['experience', 'activity'],
            'hotel'              => ['hotel'],
            'resort'             => ['resort'],
            'lodge'              => ['lodge'],
            'homestay'           => ['homestay'],
            'restaurant'         => ['restaurant'],
            'transport-provider' => ['transport'],
            'money-changer'      => ['money-changer'],
            'art-craft'          => ['art-craft'],
        ];

        foreach ($mappings as $ptSlug => $catSlugs) {
            $pt = ProviderType::where('slug', $ptSlug)->first();
            if (!$pt) continue;
            $catIds = ServiceCategory::whereIn('slug', $catSlugs)->pluck('id')->toArray();
            if (!empty($catIds)) {
                $pt->serviceCategories()->detach($catIds);
            }
        }
    }
};