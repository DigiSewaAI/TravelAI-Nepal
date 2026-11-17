<?php

namespace App\Console\Commands;

use App\Services\PlannerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DiagnoseAllRoutesData extends Command
{
    protected $signature = 'diagnose:all-routes-data
                            {--service= : Only check a specific service name/ID}
                            {--csv=storage/logs/route-data-report.csv : CSV report path}';

    protected $description = 'READ-ONLY: Scan ALL services/routes/segments/waypoints for missing data';

    private array $problems = [];
    private array $counts   = [];

    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  ALL ROUTES DATA DIAGNOSTIC (READ ONLY)');
        $this->info('====================================================');

        $this->checkTableExistence();
        $this->overviewCounts();
        $this->checkAllServices();
        $this->checkAllRoutes();
        $this->checkAllSegments();
        $this->checkAllWaypoints();
        $this->checkOrphans();
        $this->exportCsv();

        $this->printSummary();

        return self::SUCCESS;
    }

    private function checkTableExistence(): void
    {
        $this->newLine();
        $this->info('# 0. TABLE EXISTENCE');
        $this->line(str_repeat('-', 55));

        $tables = [
            'services', 'service_categories',
            'locations', 'waypoints',
            'routes', 'route_segments',
            'planner_requests', 'planner_results',
            'itinerary_days', 'itinerary_items',
            'service_itinerary_days', 'service_itinerary_items',
        ];
        foreach ($tables as $t) {
            $this->line(Schema::hasTable($t) ? "OK    {$t}" : "MISS  {$t}");
        }
    }

    private function overviewCounts(): void
    {
        $this->newLine();
        $this->info('# 1. OVERVIEW COUNTS');
        $this->line(str_repeat('-', 55));

        $this->counts = [
            'services'              => Schema::hasTable('services') ? DB::table('services')->count() : 0,
            'locations'             => Schema::hasTable('locations') ? DB::table('locations')->count() : 0,
            'waypoints'             => Schema::hasTable('waypoints') ? DB::table('waypoints')->count() : 0,
            'routes'                => Schema::hasTable('routes') ? DB::table('routes')->count() : 0,
            'route_segments'        => Schema::hasTable('route_segments') ? DB::table('route_segments')->count() : 0,
            'planner_results'       => Schema::hasTable('planner_results') ? DB::table('planner_results')->count() : 0,
            'itinerary_days'        => Schema::hasTable('itinerary_days') ? DB::table('itinerary_days')->count() : 0,
            'service_itinerary_days' => Schema::hasTable('service_itinerary_days') ? DB::table('service_itinerary_days')->count() : 0,
        ];

        foreach ($this->counts as $k => $v) {
            $this->line(sprintf('  %-25s %d', $k, $v));
        }
    }

    // ============================================================
    // 2. Every service — can PlannerService resolve a route for it?
    // ============================================================
    private function checkAllServices(): void
    {
        $this->newLine();
        $this->info('# 2. SERVICE -> ROUTE RESOLUTION');
        $this->line(str_repeat('-', 55));

        if (!Schema::hasTable('services')) {
            return;
        }

        $q = DB::table('services')->select('id', 'name', 'provider_id', 'service_category_id', 'location_id');
        if ($opt = $this->option('service')) {
            is_numeric($opt)
                ? $q->where('id', (int) $opt)
                : $q->where('name', 'like', "%{$opt}%");
        }
        $services = $q->orderBy('id')->get();

        $this->line('Total services to check: ' . $services->count());
        $this->newLine();

        $planner = app(PlannerService::class);
        $resolved  = 0;
        $unresolved = [];

        foreach ($services as $svc) {
            try {
                $route = $planner->resolveRouteForProvider($svc->name);
                if ($route) {
                    $resolved++;
                } else {
                    $unresolved[] = $svc;
                }
            } catch (\Throwable $e) {
                $unresolved[] = (object) array_merge((array) $svc, ['_err' => $e->getMessage()]);
            }
        }

        $this->line("RESOLVED:   {$resolved}");
        $this->line("UNRESOLVED: " . count($unresolved));

        if (!empty($unresolved)) {
            $this->newLine();
            $this->error('Services WITHOUT route resolution (LLM will use generic prompt, template fallback will fail):');
            foreach ($unresolved as $svc) {
                $err = isset($svc->_err) ? " [ERROR: {$svc->_err}]" : '';
                $this->line("  #{$svc->id}  loc_id=" . ($svc->location_id ?? 'NULL')
                          . "  cat_id=" . ($svc->service_category_id ?? 'NULL')
                          . "  {$svc->name}{$err}");
                $this->problems[] = [
                    'type'        => 'service_no_route',
                    'service_id'  => $svc->id,
                    'service_name'=> $svc->name,
                    'location_id' => $svc->location_id,
                    'category_id' => $svc->service_category_id,
                    'detail'      => 'resolveRouteForProvider returned NULL',
                ];
            }
        }
    }

    // ============================================================
    // 3. Every route — segment count, category, type
    // ============================================================
    private function checkAllRoutes(): void
    {
        $this->newLine();
        $this->info('# 3. ROUTES — segment counts + zero-segment detection');
        $this->line(str_repeat('-', 55));

        if (!Schema::hasTable('routes') || !Schema::hasTable('route_segments')) {
            $this->error('routes or route_segments table missing');
            return;
        }

        $routes = DB::table('routes')->orderBy('id')->get();
        $this->line('Total routes: ' . $routes->count());
        $this->newLine();

        $segCols = Schema::getColumnListing('route_segments');
        $fkCol   = in_array('route_id', $segCols, true) ? 'route_id' : null;
        if (!$fkCol) {
            $this->error('route_segments has no route_id column');
            return;
        }

        $segCounts = DB::table('route_segments')
            ->select('route_id', DB::raw('COUNT(*) as c'))
            ->groupBy('route_id')
            ->pluck('c', 'route_id')
            ->toArray();

        $zero = [];
        $ok   = 0;
        foreach ($routes as $r) {
            $count = (int) ($segCounts[$r->id] ?? 0);
            if ($count === 0) {
                $zero[] = $r;
            } else {
                $ok++;
            }
        }

        $this->line("Routes with segments: {$ok}");
        $this->line("Routes with ZERO segments: " . count($zero));

        if (!empty($zero)) {
            $this->newLine();
            $this->error('Routes with ZERO segments (template fallback will fail, verified block empty):');
            foreach ($zero as $r) {
                $this->line("  Route #{$r->id}  name={$r->name}  type=" . ($r->route_type ?? '?'));
                $this->problems[] = [
                    'type'       => 'route_zero_segments',
                    'route_id'   => $r->id,
                    'route_name' => $r->name,
                    'detail'     => 'no rows in route_segments',
                ];
            }
        }
    }

    // ============================================================
    // 4. Every segment — valid FK to waypoints, numeric distance/time
    // ============================================================
    private function checkAllSegments(): void
    {
        $this->newLine();
        $this->info('# 4. SEGMENTS — broken waypoint FKs, null distance/time');
        $this->line(str_repeat('-', 55));

        if (!Schema::hasTable('route_segments') || !Schema::hasTable('waypoints')) {
            return;
        }

        $segCols = Schema::getColumnListing('route_segments');
        $needed  = ['from_waypoint_id', 'to_waypoint_id', 'distance_km', 'estimated_time_hours'];
        foreach ($needed as $c) {
            if (!in_array($c, $segCols, true)) {
                $this->warn("route_segments missing column: {$c}");
            }
        }

        $waypointIds = DB::table('waypoints')->pluck('id')->flip()->toArray();

        $brokenFk    = [];
        $nullDist    = [];
        $nullTime    = [];

        DB::table('route_segments')->orderBy('id')->chunk(500, function ($segs) use (
            $waypointIds, &$brokenFk, &$nullDist, &$nullTime
        ) {
            foreach ($segs as $s) {
                $fid = $s->from_waypoint_id ?? null;
                $tid = $s->to_waypoint_id   ?? null;

                if (!$fid || !isset($waypointIds[$fid]) || !$tid || !isset($waypointIds[$tid])) {
                    $brokenFk[] = $s;
                }
                if (empty($s->distance_km) || $s->distance_km <= 0) {
                    $nullDist[] = $s;
                }
                if (empty($s->estimated_time_hours) || $s->estimated_time_hours <= 0) {
                    $nullTime[] = $s;
                }
            }
        });

        $this->line('Segments with broken waypoint FK: ' . count($brokenFk));
        $this->line('Segments with missing/zero distance: ' . count($nullDist));
        $this->line('Segments with missing/zero time: ' . count($nullTime));

        foreach (array_slice($brokenFk, 0, 30) as $s) {
            $this->error("  Segment #{$s->id} route_id=" . ($s->route_id ?? '?')
                       . " from={$s->from_waypoint_id} to={$s->to_waypoint_id} [BROKEN WAYPOINT]");
            $this->problems[] = [
                'type'   => 'segment_broken_waypoint',
                'segment_id' => $s->id,
                'route_id'   => $s->route_id ?? null,
                'detail' => "from={$s->from_waypoint_id} to={$s->to_waypoint_id}",
            ];
        }
        if (count($brokenFk) > 30) {
            $this->line('  ... (+' . (count($brokenFk) - 30) . ' more, see CSV)');
        }

        foreach (array_slice($nullDist, 0, 15) as $s) {
            $this->warn("  Segment #{$s->id} route_id={$s->route_id} distance_km=" . var_export($s->distance_km, true));
        }
        foreach (array_slice($nullTime, 0, 15) as $s) {
            $this->warn("  Segment #{$s->id} route_id={$s->route_id} estimated_time_hours=" . var_export($s->estimated_time_hours, true));
        }

        // CSV rows
        foreach ($brokenFk as $s) {
            $this->problems[] = ['type' => 'segment_broken_waypoint', 'segment_id' => $s->id, 'route_id' => $s->route_id ?? null, 'detail' => 'broken FK'];
        }
        foreach ($nullDist as $s) {
            $this->problems[] = ['type' => 'segment_null_distance', 'segment_id' => $s->id, 'route_id' => $s->route_id ?? null, 'detail' => 'distance_km <= 0'];
        }
        foreach ($nullTime as $s) {
            $this->problems[] = ['type' => 'segment_null_time', 'segment_id' => $s->id, 'route_id' => $s->route_id ?? null, 'detail' => 'estimated_time_hours <= 0'];
        }
    }

    // ============================================================
    // 5. Every waypoint — altitude/location_id/type
    // ============================================================
    private function checkAllWaypoints(): void
    {
        $this->newLine();
        $this->info('# 5. WAYPOINTS — null altitude / null location / null type');
        $this->line(str_repeat('-', 55));

        if (!Schema::hasTable('waypoints')) {
            return;
        }

        $cols = Schema::getColumnListing('waypoints');
        $this->line('Total waypoints: ' . DB::table('waypoints')->count());

        foreach (['altitude', 'location_id', 'type'] as $c) {
            if (!in_array($c, $cols, true)) {
                $this->warn("waypoints.{$c} column missing");
                continue;
            }
            $nullCount = DB::table('waypoints')->whereNull($c)->count();
            $this->line("  NULL {$c}: {$nullCount}");
            if ($nullCount > 0) {
                $this->problems[] = [
                    'type'   => 'waypoint_null_' . $c,
                    'detail' => "{$nullCount} waypoints have NULL {$c}",
                ];
            }
        }
    }

    // ============================================================
    // 6. Orphans — segments pointing to missing routes, waypoints in no route
    // ============================================================
    private function checkOrphans(): void
    {
        $this->newLine();
        $this->info('# 6. ORPHANS');
        $this->line(str_repeat('-', 55));

        if (Schema::hasTable('route_segments') && Schema::hasTable('routes')) {
            $orphanSegs = DB::table('route_segments')
                ->leftJoin('routes', 'route_segments.route_id', '=', 'routes.id')
                ->whereNull('routes.id')
                ->count();
            $this->line('Orphan segments (no route): ' . $orphanSegs);
            if ($orphanSegs > 0) {
                $this->problems[] = ['type' => 'orphan_segments', 'detail' => "{$orphanSegs} segments with missing route_id"];
            }
        }

        // Waypoints never referenced by any segment (informational)
        if (Schema::hasTable('waypoints') && Schema::hasTable('route_segments')) {
            $unusedWp = DB::table('waypoints')
                ->leftJoin('route_segments as a', 'waypoints.id', '=', 'a.from_waypoint_id')
                ->leftJoin('route_segments as b', 'waypoints.id', '=', 'b.to_waypoint_id')
                ->whereNull('a.id')->whereNull('b.id')
                ->count();
            $this->line('Waypoints never used in any segment: ' . $unusedWp);
        }
    }

    private function exportCsv(): void
    {
        $path = base_path((string) ($this->option('csv') ?: 'storage/logs/route-data-report.csv'));
        File::ensureDirectoryExists(dirname($path));

        $fh = fopen($path, 'w');
        fputcsv($fh, ['type', 'service_id', 'service_name', 'route_id', 'route_name', 'segment_id', 'location_id', 'category_id', 'detail']);
        foreach ($this->problems as $p) {
            fputcsv($fh, [
                $p['type']         ?? '',
                $p['service_id']   ?? '',
                $p['service_name'] ?? '',
                $p['route_id']     ?? '',
                $p['route_name']   ?? '',
                $p['segment_id']   ?? '',
                $p['location_id']  ?? '',
                $p['category_id']  ?? '',
                $p['detail']       ?? '',
            ]);
        }
        fclose($fh);

        $this->newLine();
        $this->info("CSV report: {$path}");
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('====================================================');
        $this->info('  SUMMARY');
        $this->info('====================================================');

        $byType = [];
        foreach ($this->problems as $p) {
            $byType[$p['type']] = ($byType[$p['type']] ?? 0) + 1;
        }

        if (empty($byType)) {
            $this->info('No problems found.');
            return;
        }

        arsort($byType);
        foreach ($byType as $type => $count) {
            $this->error(sprintf('  %-30s %d', $type, $count));
        }
        $this->line('Total problems: ' . count($this->problems));
    }
}