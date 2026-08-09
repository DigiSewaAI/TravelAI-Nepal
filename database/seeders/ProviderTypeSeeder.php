<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProviderType;

class ProviderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Trekking Agency', 'slug' => 'trekking-agency'],
            ['name' => 'Tour Agency', 'slug' => 'tour-agency'],
            ['name' => 'Hotel', 'slug' => 'hotel'],
            ['name' => 'Resort', 'slug' => 'resort'],
            ['name' => 'Lodge', 'slug' => 'lodge'],
            ['name' => 'Homestay', 'slug' => 'homestay'],
            ['name' => 'Guide', 'slug' => 'guide'],
            ['name' => 'Porter', 'slug' => 'porter'],
            ['name' => 'Transport Provider', 'slug' => 'transport-provider'],
            ['name' => 'Activity Provider', 'slug' => 'activity-provider'],
            ['name' => 'Local Experience', 'slug' => 'local-experience'],
            ['name' => 'Photographer', 'slug' => 'photographer'],
        ];

        foreach ($types as $type) {
            ProviderType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}