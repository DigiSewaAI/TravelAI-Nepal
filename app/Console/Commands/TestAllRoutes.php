<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use App\Models\RouteSegment;

class TestAllRoutes extends Command
{
    protected $signature = 'test:all-routes {--detailed : Show segment details}';
    protected $description = 'Check all routes for segment completeness (CRITICAL/SEVERE/WARNING/OK)';

    public function handle()
    {
        $this->info('🔍 Checking all routes...');
        $this->newLine();

        $routes = Route::where('is_active', true)->orderBy('name')->get();

        $critical = []; // 0 segments
        $severe = [];   // < 50% expected
        $warning = [];  // 50-89% expected
        $ok = [];       // >= 90% expected

        foreach ($routes as $route) {
            $segments = RouteSegment::where('route_id', $route->id)->count();
            $days = $route->duration_days;

            // Calculate expected minimum segments
            // For a tour/trek of N days, minimum should be:
            // - At least 1 segment (even 1-day tours need start→end)
            // - Ideally N segments for N days (1 segment per day)
            $minExpected = max(1, (int) ceil($days * 0.5)); // 50% of days, minimum 1

            if ($segments === 0) {
                $critical[] = ['route' => $route, 'segments' => $segments, 'min' => $minExpected];
            } elseif ($segments < $minExpected) {
                $severe[] = ['route' => $route, 'segments' => $segments, 'min' => $minExpected];
            } elseif ($segments < ($days - 1)) {
                $warning[] = ['route' => $route, 'segments' => $segments, 'expected' => $days - 1];
            } else {
                $ok[] = ['route' => $route, 'segments' => $segments];
            }
        }

        // ============================================
        // Print CRITICAL (0 segments)
        // ============================================
        if (!empty($critical)) {
            $this->error('🔴 CRITICAL (0 segments – needs immediate fix):');
            foreach ($critical as $item) {
                $this->line("   ❌ {$item['route']->name} ({$item['route']->duration_days}d) – 0 seg – MIN NEEDED: {$item['min']}");
            }
            $this->newLine();
        }

        // ============================================
        // Print SEVERE (< 50%)
        // ============================================
        if (!empty($severe)) {
            $this->warn('🟠 SEVERE (< 50% expected):');
            foreach ($severe as $item) {
                $this->line("   ⚠️  {$item['route']->name} ({$item['route']->duration_days}d) – {$item['segments']} seg – NEED: {$item['min']}+");
            }
            $this->newLine();
        }

        // ============================================
        // Print WARNING (50-89%)
        // ============================================
        if (!empty($warning)) {
            $this->line('🟡 WARNING (50-89% expected):');
            foreach ($warning as $item) {
                $this->line("   ⚡ {$item['route']->name} ({$item['route']->duration_days}d) – {$item['segments']} seg");
            }
            $this->newLine();
        }

        // ============================================
        // Print OK (>= 90%)
        // ============================================
        if ($this->option('detailed') && !empty($ok)) {
            $this->info('🟢 OK:');
            foreach ($ok as $item) {
                $this->line("   ✅ {$item['route']->name} ({$item['route']->duration_days}d) – {$item['segments']} seg");
            }
            $this->newLine();
        }

        // ============================================
        // Summary
        // ============================================
        $this->newLine();
        $this->info('📊 Summary:');
        $this->line("   🔴 Critical: " . count($critical));
        $this->line("   🟠 Severe:   " . count($severe));
        $this->line("   🟡 Warning:  " . count($warning));
        $this->line("   🟢 OK:       " . count($ok));
        $this->line("   📋 Total:    " . $routes->count());

        // ============================================
        // Return exit code
        // ============================================
        $hasCritical = count($critical) > 0;
        $hasSevere = count($severe) > 0;

        if ($hasCritical || $hasSevere) {
            return 1;
        }
        return 0;
    }
}