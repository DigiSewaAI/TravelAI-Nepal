<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\ProviderType;
use Illuminate\Database\Seeder;

class AssignProviderTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Define mapping: provider name keywords → provider type slug
        $mapping = [
            'trekking' => 'trekking-agency',
            'himalayan' => 'trekking-agency',
            'everest' => 'trekking-agency',
            'annapurna' => 'trekking-agency',
            'trailblazers' => 'trekking-agency',
            'hotel' => 'hotel',
            'pokhara' => 'hotel',
            'resort' => 'resort',
            'lodge' => 'lodge',
            'homestay' => 'homestay',
            'guide' => 'guide',
            'porter' => 'porter',
            'transport' => 'transport-provider',
            'travel' => 'tour-agency',
            'tour' => 'tour-agency',
            'adventure' => 'activity-provider',
            'activity' => 'activity-provider',
            'local' => 'local-experience',
            'photographer' => 'photographer',
        ];

        // Get all provider types
        $types = ProviderType::all()->keyBy('slug');

        // Loop through all providers
        Provider::all()->each(function ($provider) use ($mapping, $types) {
            $assigned = false;
            $name = strtolower($provider->name);

            foreach ($mapping as $keyword => $slug) {
                if (str_contains($name, $keyword)) {
                    if (isset($types[$slug])) {
                        $provider->types()->sync([$types[$slug]->id]);
                        $assigned = true;
                        $this->command->info("Assigned '{$slug}' to {$provider->name}");
                        break;
                    }
                }
            }

            // If no match, assign default: Trekking Agency (ID 1)
            if (!$assigned) {
                $default = ProviderType::first();
                if ($default) {
                    $provider->types()->sync([$default->id]);
                    $this->command->info("Assigned default '{$default->slug}' to {$provider->name}");
                }
            }
        });
    }
}