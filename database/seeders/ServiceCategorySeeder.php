<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Trek', 'slug' => 'trek'],
            ['name' => 'Tour', 'slug' => 'tour'],
            ['name' => 'Hotel', 'slug' => 'hotel'],
            ['name' => 'Guide', 'slug' => 'guide'],
            ['name' => 'Transport', 'slug' => 'transport'],
            ['name' => 'Activity', 'slug' => 'activity'],
            ['name' => 'Experience', 'slug' => 'experience'],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}