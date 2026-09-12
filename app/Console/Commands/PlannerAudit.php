<?php

namespace App\Console\Commands;

use App\Models\Route;
use App\Services\PlannerService;
use Illuminate\Console\Command;

class PlannerAudit extends Command
{
    protected $signature = 'planner:audit
                            {--route= : Specific route slug to audit}
                            {--limit= : Limit number of routes}
                            {--skip-itinerary : Skip itinerary generation (faster)}
                            {--json : Output as JSON}';

    protected $description = 'Read-only audit: segments, costs, itinerary for all active routes';

    public function handle(): int
    {
        $query = Route::where('is_active', 1)->orderBy('slug');

        if ($slug = $this->option('route')) {
            $query->where('slug', $slug);
        }
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $routes = $query->get();
        $skipItinerary = (bool) $this->option('skip-itinerary');

        $this->info("🔍 Auditing {$routes->count()} active routes (READ-ONLY)...");
        if ($skipItinerary) {
            $this->warn("   (itinerary generation skipped)");
        }
        $this->newLine();

        $results = [];
        $bar = $this->output->createProgressBar($routes->count());
        $bar->start();

        foreach ($routes as $route) {
            $results[] = $this->auditRoute($route, $skipItinerary);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->renderResults($results);
        return self::SUCCESS;
    }

    private function auditRoute(Route $route, bool $skipItinerary): array
    {
        $segments = $route->segments()->count();
        $restDays = $route->segments()->where('distance_km', 0)->count();
        $costs = $route->costs()->count();

        $itineraryDays = null;
        $itineraryError = null;

        if (!$skipItinerary) {
            try {
                $planner = app(PlannerService::class);
                $result = $planner->generate([
                    'destination'   => $route->slug,
                    'days'          => $route->duration_days,
                    'budget'        => 300000,
                    'travel_style'  => 'mid_range',
                ]);

                if (is_array($result) && isset($result['days'])) {
                    $itineraryDays = $this->normalizeCount($result['days']);
                } elseif (is_array($result) && isset($result['itinerary'])) {
                    $itineraryDays = $this->normalizeCount($result['itinerary']);
                } else {
                    $itineraryDays = 0;
                }
            } catch (\Throwable $e) {
                $itineraryError = class_basename($e) . ': ' . substr($e->getMessage(), 0, 80);
            }
        }

        $status = 'PASS';
        $notes = [];

        if ($segments === 0) {
            $status = 'FAIL';
            $notes[] = 'no-segments';
        }

        if ($costs === 0) {
            $status = ($status === 'FAIL') ? 'FAIL' : 'WARN';
            $notes[] = 'no-costs';
        }

        if ($itineraryError !== null) {
            $status = 'FAIL';
            $notes[] = 'exception';
        } elseif ($itineraryDays !== null) {
            if ($itineraryDays === 0) {
                $status = 'FAIL';
                $notes[] = 'no-itinerary';
            } elseif ($itineraryDays < $route->duration_days) {
                $status = ($status === 'FAIL') ? 'FAIL' : 'WARN';
                $notes[] = "days-{$itineraryDays}/{$route->duration_days}";
            }
        }

        return [
            'slug'            => $route->slug,
            'duration'        => $route->duration_days,
            'segments'        => $segments,
            'rest'            => $restDays,
            'costs'           => $costs,
            'itinerary'       => $itineraryDays,
            'itinerary_error' => $itineraryError,
            'status'          => $status,
            'notes'           => implode(', ', $notes),
        ];
    }

    private function normalizeCount($value): int
    {
        if (is_array($value)) {
            return count($value);
        }
        if ($value instanceof \Countable) {
            return $value->count();
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        return 0;
    }

    private function renderResults(array $results): void
    {
        $this->table(
            ['Route', 'Dur', 'Segs', 'Rest', 'Costs', 'Itin', 'Status', 'Notes'],
            array_map(fn($r) => [
                $r['slug'],
                $r['duration'],
                $r['segments'],
                $r['rest'],
                $r['costs'],
                $r['itinerary'] ?? '—',
                $r['status'],
                $r['notes'] ?: '—',
            ], $results)
        );

        $pass = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
        $warn = count(array_filter($results, fn($r) => $r['status'] === 'WARN'));
        $fail = count(array_filter($results, fn($r) => $r['status'] === 'FAIL'));

        $this->newLine();
        $this->info('📊 Summary:');
        $this->line("   ✅ PASS: {$pass}");
        $this->line("   ⚠️  WARN: {$warn}");
        $this->line("   ❌ FAIL: {$fail}");

        if ($fail > 0) {
            $this->newLine();
            $this->error('❌ FAIL routes:');
            foreach ($results as $r) {
                if ($r['status'] === 'FAIL') {
                    $this->line("   - {$r['slug']}: {$r['notes']}");
                }
            }
        }

        if ($warn > 0) {
            $this->newLine();
            $this->warn('⚠️  WARN routes:');
            foreach ($results as $r) {
                if ($r['status'] === 'WARN') {
                    $this->line("   - {$r['slug']}: {$r['notes']}");
                }
            }
        }
    }
}