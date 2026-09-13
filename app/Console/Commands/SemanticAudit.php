<?php

namespace App\Console\Commands;

use App\Models\Route;
use App\Models\Waypoint;
use Illuminate\Console\Command;

class SemanticAudit extends Command
{
    protected $signature = 'planner:semantic-audit
                            {--route= : Specific route slug}
                            {--json : JSON output}';

    protected $description = 'Refined semantic audit — only real data issues (no false positives)';

    public function handle(): int
    {
        $query = Route::where('is_active', 1)->orderBy('slug');
        if ($slug = $this->option('route')) {
            $query->where('slug', $slug);
        }

        $routes = $query->get();
        $this->info("🔍 Refined semantic audit on {$routes->count()} routes...\n");

        $results = [];
        $bar = $this->output->createProgressBar($routes->count());
        $bar->start();

        foreach ($routes as $route) {
            $results[] = $this->auditRoute($route);
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

    private function auditRoute(Route $route): array
    {
        $segments = $route->segments()->orderBy('sequence')->get();
        $issues = [];

        if ($segments->isEmpty()) {
            return [
                'slug' => $route->slug,
                'name' => $route->name,
                'type' => 'unknown',
                'dur' => $route->duration_days,
                'segs' => 0,
                'status' => 'FAIL',
                'issues' => ['no-segments'],
            ];
        }

        // ─── Classify route type ───
        $routeType = $this->classifyRoute($route, $segments);

        // ─── Check 1: Trek — peak altitude reaches route max_altitude ───
        if ($routeType === 'trek') {
            $maxWpAlt = 0;
            $maxAltSeq = 0;
            foreach ($segments as $s) {
                $wp = Waypoint::find($s->to_waypoint_id);
                if ($wp && $wp->altitude > $maxWpAlt) {
                    $maxWpAlt = $wp->altitude;
                    $maxAltSeq = $s->sequence;
                }
            }
            if ($route->max_altitude > 0 && $maxWpAlt > 0) {
                $diff = abs($route->max_altitude - $maxWpAlt);
                if ($diff > 500) {
                    $issues[] = "peak-alt-mismatch (route: {$route->max_altitude}m, waypoints: {$maxWpAlt}m)";
                }
            }
            // Peak should not be at last segment for treks (need return)
            if ($maxAltSeq === $segments->count() && $segments->count() > 4) {
                $issues[] = "peak-at-end (no return from peak)";
            }
        }

        // ─── Check 2: Tour — should have return (round-trip) ───
                // Phase 4R-fix-11: skip 1-day tours (no return needed); allow same location_id
        if ($routeType === 'tour' && $route->duration_days > 1 && $route->duration_days <= 2) {
            $first = $segments->first();
            $last = $segments->last();
            $firstWp = Waypoint::find($first->from_waypoint_id);
            $lastWp  = Waypoint::find($last->to_waypoint_id);
            $isRoundTrip = ($first->from_waypoint_id === $last->to_waypoint_id)
                || ($firstWp && $lastWp && $firstWp->location_id !== null
                    && $firstWp->location_id === $lastWp->location_id);
            if (!$isRoundTrip) {
                $issues[] = "tour-no-return (first != last)";
            }
        }

        // ─── Check 3: Circuit/Base Camp naming vs structure ───
        $nameLower = strtolower($route->name);
        $first = $segments->first();
        $last = $segments->last();
        $isRoundTrip = $first->from_waypoint_id === $last->to_waypoint_id;

                // Phase 4R-fix-11: only flag TOURS (trek circuits are loop-style, no return expected)
        if ($routeType === 'tour' && str_contains($nameLower, 'circuit') && !$isRoundTrip && $route->duration_days > 3) {
            $issues[] = "circuit-no-return";
        }
        if (str_contains($nameLower, 'base camp') || str_contains($nameLower, 'base-camp')) {
            $maxWpAlt = 0;
            foreach ($segments as $s) {
                $wp = Waypoint::find($s->to_waypoint_id);
                if ($wp && $wp->altitude > $maxWpAlt) $maxWpAlt = $wp->altitude;
            }
            if ($maxWpAlt < 3000) {
                $issues[] = "base-camp-no-altitude (max: {$maxWpAlt}m)";
            }
        }

        // ─── Check 4: Segment count vs duration ───
        // Treks: at least 70% of days should have segments
        // Tours: 100% (each day a segment)
        $minRatio = ($routeType === 'trek') ? 0.7 : 0.9;
        $minSegs = (int) ceil($route->duration_days * $minRatio);
        if ($segments->count() < $minSegs && $route->duration_days > 2) {
            $issues[] = "too-few-segs (dur {$route->duration_days}d, segs {$segments->count()}, need {$minSegs})";
        }

                // ─── Check 5: Distance/speed — only flag truly unrealistic ───
        foreach ($segments as $s) {
            if ($s->distance_km <= 0 || $s->estimated_time_hours <= 0) continue;
            $speed = $s->distance_km / $s->estimated_time_hours;

            // Walking tier (short distance): 1-7 km/h realistic
            if ($s->distance_km < 25) {
                // Phase 4R-fix-14: only treks require walking-speed validation.
                // Tours use vehicles (20-40 km/h realistic); activities have
                // their own speeds (zipline 50+ km/h, rafting 5-10 km/h).
                if ($routeType !== 'trek') continue;

                if ($speed > 8) {
                    $issues[] = "walking-speed-unrealistic (seq {$s->sequence}: {$s->distance_km}km/{$s->estimated_time_hours}hr = " . round($speed, 1) . " km/h)";
                } elseif ($speed < 1 && $s->distance_km > 3) {
                    $issues[] = "walking-speed-too-slow (seq {$s->sequence}: " . round($speed, 1) . " km/h)";
                }
            }
            // Vehicle tier (long distance): 15-60 km/h realistic
                        else {
                if ($speed > 70) {
                    $issues[] = "vehicle-speed-unrealistic (seq {$s->sequence}: " . round($speed, 1) . " km/h)";
                } elseif ($speed < 8 && $routeType === 'tour') {
                    // Phase 4R-fix-12: treks allow slow walking (remote Himalaya)
                    $issues[] = "long-dist-too-slow (seq {$s->sequence}: {$s->distance_km}km/" . $s->estimated_time_hours . "hr)";
                }
            }
        }

        // ─── Check 6: Waypoint location_id — only for village/city/checkpoint ───
        $wpsNeedingLoc = ['village', 'city', 'checkpoint'];
        foreach ($segments as $s) {
            $to = Waypoint::find($s->to_waypoint_id);
            if ($to && in_array($to->type, $wpsNeedingLoc) && !$to->location_id) {
                $issues[] = "loc-missing ({$to->name}, {$to->type})";
            }
        }

        // ─── Check 7: Self-loop with distance (real bug) ───
        foreach ($segments as $s) {
            if ($s->from_waypoint_id === $s->to_waypoint_id && $s->distance_km > 1) {
                $issues[] = "self-loop-with-distance (seq {$s->sequence}: " . $s->distance_km . "km)";
            }
        }

        // ─── Check 8: Costs presence ───
        if ($route->costs()->count() === 0) {
            $issues[] = "no-costs";
        }

        // ─── Check 9: Generic slug waypoints (only if >3 segs) ───
        if ($segments->count() > 3) {
            foreach ($segments as $s) {
                $from = Waypoint::find($s->from_waypoint_id);
                $to = Waypoint::find($s->to_waypoint_id);
                foreach ([$from, $to] as $wp) {
                                        // Phase 4R-fix-11: exclude descriptive slugs like *-tour-start
                    if ($wp && preg_match('/-(start|end)$/', $wp->slug)
                        && !str_contains($wp->slug, 'tour')) {
                        $issues[] = "generic-wp-slug ({$wp->slug})";
                        break 2;
                    }
                }
            }
        }

        $status = empty($issues) ? 'PASS' : 'ISSUES';
        return [
            'slug' => $route->slug,
            'name' => $route->name,
            'type' => $routeType,
            'dur' => $route->duration_days,
            'segs' => $segments->count(),
            'status' => $status,
            'issues' => $issues,
        ];
    }

    private function classifyRoute(Route $route, $segments): string
    {
        // Activity = has activity keywords in slug
        $activityKeywords = ['rafting', 'bungee', 'zipline', 'skydiving', 'paragliding', 'ballooning', 'kayaking', 'canyoning', 'climbing', 'biking'];
        foreach ($activityKeywords as $kw) {
            if (str_contains($route->slug, $kw)) return 'activity';
        }

        // Trek = duration >= 3 AND max_alt > 1500
                // Phase 4R-fix-15: pilgrimage routes use vehicles (flights/jeeps), not walking
        if (str_contains($route->slug, 'pilgrimage')) {
            return 'tour';
        }

        if ($route->duration_days >= 3 && $route->max_altitude > 1500) {
            return 'trek';
        }

        // Otherwise tour
        return 'tour';
    }

    private function renderResults(array $results): void
    {
        $pass = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
        $issues = count(array_filter($results, fn($r) => $r['status'] === 'ISSUES'));
        $fail = count(array_filter($results, fn($r) => $r['status'] === 'FAIL'));

        // Only show routes with issues (not PASS)
        $problemRoutes = array_filter($results, fn($r) => $r['status'] !== 'PASS');

        if (!empty($problemRoutes)) {
            $this->table(
                ['Route', 'Type', 'Dur', 'Segs', 'Status', 'Issues'],
                array_map(fn($r) => [
                    substr($r['slug'], 0, 30),
                    $r['type'],
                    $r['dur'],
                    $r['segs'],
                    $r['status'],
                    count($r['issues']) > 0 ? implode(', ', array_slice($r['issues'], 0, 2)) : '—',
                ], $problemRoutes)
            );
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("   ✅ PASS: {$pass}");
        $this->line("   ⚠️  ISSUES: {$issues}");
        $this->line("   ❌ FAIL: {$fail}");

        if ($issues + $fail > 0) {
            $this->newLine();
            $this->error("❌ Real issues found:\n");
            foreach ($results as $r) {
                if ($r['status'] !== 'PASS') {
                    $this->line("   ● {$r['slug']} [{$r['type']}] ({$r['name']}):");
                    foreach ($r['issues'] as $issue) {
                        $this->line("      - {$issue}");
                    }
                }
            }
        }
    }
}