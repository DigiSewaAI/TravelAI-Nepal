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

            if (preg_match('/\b(Rafting|Paragliding|Bungee|Bungee Jumping|Zip-?line|Zip-?lining|Kayaking|Skydiving|Canyoning|Rock Climbing|Mountain Biking|Hot Air Ballooning)\b/i', $name)) {
                $type = 'activity';
            } elseif (stripos($name, 'Safari') !== false) {
                $type = 'tour';
            } elseif (preg_match('/\bTrek\b/i', $name)) {
                $type = 'trek';
            } elseif (preg_match('/\b(Tour|Heritage|Pilgrimage|Sightseeing)\b/i', $name)) {
                $type = 'tour';
            } elseif (preg_match('/\b(Village|City|Hill Station|Tea Garden)\b/i', $name)) {
                $type = 'tour';
            } elseif (preg_match('/\b(Circuit|Pass|Base Camp)\b/i', $name)) {
                $type = 'trek';
            } else {
                $type = 'trek';
            }

            $manualOverrides = [
                'kanchenjunga-circuit' => 'trek',
                'annapurna-circuit' => 'trek',
                'dolpo-circuit' => 'trek',
                'lumbini-circuit' => 'tour',
                'lumbini-mayadevi' => 'tour',
                'pathibhara-temple-pilgrimage' => 'tour',
                'muktinath-pilgrimage' => 'tour',
                'janaki-temple-pilgrimage' => 'tour',
                'mardi-himal' => 'trek',
                'gokyo-lakes' => 'trek',
                'three-passes' => 'trek',
                'upper-mustang' => 'trek',
                'lower-mustang' => 'trek',
                'jomsom-muktinath' => 'trek',
                'khopra-ridge' => 'trek',
                'mohare-danda' => 'trek',
                'sikles' => 'trek',
                'panchase' => 'trek',
            ];
            if (isset($manualOverrides[$route->slug])) {
                $type = $manualOverrides[$route->slug];
            }

            $route->route_type = $type;

            $catSlug = match ($type) {
                'trek' => 'trek',
                'tour' => 'tour',
                'activity' => 'activity',
                'service' => 'experience',
                default => 'trek',
            };

            if (isset($categories[$catSlug])) {
                $route->service_category_id = $categories[$catSlug];
            }

            $route->save();
        });

        $this->command->info('✅ Route types & categories assigned.');

        $distribution = Route::select('route_type')
            ->groupBy('route_type')
            ->selectRaw('route_type, count(*) as cnt')
            ->get();
        foreach ($distribution as $d) {
            $this->command->info("   {$d->route_type}: {$d->cnt}");
        }
    }
}