<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class AssignRouteCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ServiceCategory::pluck('id', 'slug');

        Route::all()->each(function ($route) use ($categories) {
            $name = $route->name;

            // Determine category based on route name
            if (stripos($name, 'Trek') !== false || stripos($name, 'trek') !== false) {
                $cat = 'trek';
            } elseif (stripos($name, 'Tour') !== false || stripos($name, 'tour') !== false ||
                      stripos($name, 'Safari') !== false || stripos($name, 'Heritage') !== false ||
                      stripos($name, 'Circuit') !== false || stripos($name, 'Pilgrimage') !== false ||
                      stripos($name, 'City') !== false || stripos($name, 'Lakeside') !== false) {
                $cat = 'tour';
            } elseif (stripos($name, 'Rafting') !== false || stripos($name, 'Paragliding') !== false ||
                      stripos($name, 'Bungee') !== false || stripos($name, 'Activity') !== false ||
                      stripos($name, 'Adventure') !== false) {
                $cat = 'activity';
            } else {
                $cat = 'trek'; // default
            }

            if (isset($categories[$cat])) {
                $route->service_category_id = $categories[$cat];
                $route->save();
            }
        });

        $this->command->info('✅ Route categories assigned.');
        $this->command->info('   📌 Total routes updated: ' . Route::count());
    }
}