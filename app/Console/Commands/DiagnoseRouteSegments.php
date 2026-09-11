<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;

class DiagnoseRouteSegments extends Command
{
    protected $signature = 'planner:diagnose-segments {--priority : Only priority routes}';
    protected $description = 'Classify routes by segment quality — detect hacks, gaps, missing returns';

    public function handle()
    {
        $this->info('🔍 Diagnosing route segment quality...');
        $this->newLine();

                $prioritySlugs = [
            'annapurna-circuit',
            'kanchenjunga-north',
            'kanchenjunga-south',
            'kanchenjunga-circuit',
            'api-himal',
            'makalu-base-camp',
            'makalu-barun',
            'bardiya-trek',
            'dolpo-circuit',
            'upper-dolpo',
            'phoksundo-lake',
            'humla',
            'damodar-kunda',
            'rolwaling',
            'saipal',
        ];

        $query = Route::where('is_active', true)->withCount('segments');

        if ($this->option('priority')) {
            $query->whereIn('slug', $prioritySlugs);
        }

        $routes = $query->get()->sortByDesc(fn($r) => $r->duration_days - $r->segments_count);

        $categoryA = [];
        $categoryB = [];
        $categoryC = [];

        foreach ($routes as $route) {
            // ✅ Force relationship query — avoids 'segments' JSON cast conflict
            $routeSegments = $route->segments()->get();

            $gap = $route->duration_days - $route->segments_count;
            $maxSegDist = $routeSegments->max('distance_km') ?? 0;
            $zeroDistSegs = $routeSegments->where('distance_km', 0)->count();

            $info = [
                'name' => $route->name,
                'slug' => $route->slug,
                'days' => $route->duration_days,
                'rec_days' => $route->recommended_days,
                'segs' => $route->segments_count,
                'gap' => $gap,
                'max_dist' => $maxSegDist,
                'rest_days' => $zeroDistSegs,
            ];

                        if ($maxSegDist > 25) {
                $categoryC[] = $info;
            } elseif ($gap > 0 && $zeroDistSegs == 0) {
                $categoryB[] = $info;
            } else {
                $categoryA[] = $info;
            }
        }

        $this->renderCategory('🟢 CATEGORY A — Legitimate (gap = rest days or zero)', $categoryA);
        $this->renderCategory('🟠 CATEGORY B — Missing segments (expand with real data)', $categoryB);
        $this->renderCategory('🔴 CATEGORY C — Single-segment hacks (split into real stops)', $categoryC);

        $this->newLine();
        $this->info('📊 Summary:');
        $this->line("   Category A: " . count($categoryA));
        $this->line("   Category B: " . count($categoryB));
        $this->line("   Category C: " . count($categoryC));
        $this->line("   Total:      " . $routes->count());

        return 0;
    }

    private function renderCategory(string $title, array $items): void
    {
        if (empty($items)) {
            $this->line($title . ' — (none)');
            $this->newLine();
            return;
        }

        $this->line($title);
        $this->table(
            ['Route', 'Days', 'Rec', 'Segs', 'Gap', 'Max km', 'Rest'],
            array_map(fn($i) => [
                \Str::limit($i['name'], 40),
                $i['days'],
                $i['rec_days'] ?? '—',
                $i['segs'],
                $i['gap'],
                $i['max_dist'],
                $i['rest_days'],
            ], $items)
        );
        $this->newLine();
    }
}