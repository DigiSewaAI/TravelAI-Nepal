<?php

namespace App\Console\Commands;

use App\Services\PlannerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DiagnoseAllRoutesQuality extends Command
{
    protected $signature = 'diagnose:all-routes-quality
                            {--csv=storage/logs/route-quality-report.csv}';

    protected $description = 'READ-ONLY: structural + known-detour quality check across ALL routes';

    private array $problems = [];

    // Famous treks ma hunchha hune "detour" waypoints jasko LLM le skip garna sakchha
    private array $knownDetours = [
        'annapurna base camp' => ['poon hill', 'tadapani'],
        'abc'                 => ['poon hill', 'tadapani'],
        'everest base camp'   => ['kala patthar'],
        'ebc'                 => ['kala patthar'],
        'annapurna circuit'   => ['thorong la', 'thorung la', 'thorong phedi'],
        'manaslu'             => ['larke pass', 'larkya la'],
        'langtang'            => ['kyanjin ri', 'kyangjin ri'],
        'mardi himal'         => ['high camp', 'mardi himal base camp'],
        'gokyo'               => ['gokyo ri'],
        'island peak'         => ['island peak', 'imja tse'],
        'poon hill'           => ['poon hill'],
        'annapurna panorama'  => ['poon hill', 'ghorepani'],
    ];

    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('  ALL ROUTES QUALITY DIAGNOSTIC (READ ONLY)');
        $this->info('====================================================');

        $this->checkZeroSegmentRoutes();
        $this->checkZeroDistanceSegments();
        $this->checkDisconnectedChains();
        $this->checkDuplicateSegments();
        $this->checkKnownDetours();
        $this->dumpAbcDetail();
        $this->exportCsv();
        $this->summary();

        return self::SUCCESS;
    }

    private function checkZeroSegmentRoutes(): void
    {
        $this->newLine();
        $this->info('# 1. ZERO-SEGMENT ROUTES');
        $this->line(str_repeat('-', 55));

        $routes = DB::table('routes')->get();
        $segCounts = DB::table('route_segments')
            ->select('route_id', DB::raw('COUNT(*) as c'))
            ->groupBy('route_id')
            ->pluck('c', 'route_id')
            ->toArray();

        $found = 0;
        foreach ($routes as $r) {
            if (($segCounts[$r->id] ?? 0) === 0) {
                $this->warn("Route #{$r->id}  {$r->name}  type=" . ($r->route_type ?? '?'));
                $this->problems[] = ['type' => 'zero_segments', 'route_id' => $r->id, 'segment_id' => '', 'detail' => $r->name];
                $found++;
            }
        }
        if ($found === 0) $this->info('None.');
    }

    private function checkZeroDistanceSegments(): void
    {
        $this->newLine();
        $this->info('# 2. ZERO-DISTANCE SEGMENTS: acclimatization vs real gap');
        $this->line(str_repeat('-', 55));

        $sameWp = DB::table('route_segments')
            ->where(function ($q) { $q->where('distance_km', '<=', 0)->orWhereNull('distance_km'); })
            ->whereColumn('from_waypoint_id', '=', 'to_waypoint_id')
            ->count();

        $diffWp = DB::table('route_segments as rs')
            ->join('waypoints as fw', 'rs.from_waypoint_id', '=', 'fw.id')
            ->join('waypoints as tw', 'rs.to_waypoint_id', '=', 'tw.id')
            ->join('routes as r', 'rs.route_id', '=', 'r.id')
            ->where(function ($q) {
                $q->where('rs.distance_km', '<=', 0)->orWhereNull('rs.distance_km');
            })
            ->whereColumn('rs.from_waypoint_id', '!=', 'rs.to_waypoint_id')
            ->select('rs.id as seg_id', 'rs.route_id', 'r.name as route_name',
                     'fw.name as from_n', 'tw.name as to_n',
                     'rs.distance_km', 'rs.estimated_time_hours')
            ->get();

        $this->line("Same from/to waypoint (legit acclimatization): {$sameWp}");
        $this->line("Different waypoints (REAL data gap): " . $diffWp->count());

        if ($diffWp->count() > 0) {
            $this->newLine();
            $this->warn('Real gap segments:');
            foreach ($diffWp as $s) {
                $this->line("  seg#{$s->seg_id} route={$s->route_name} {$s->from_n} -> {$s->to_n}  dist={$s->distance_km} time={$s->estimated_time_hours}");
                $this->problems[] = [
                    'type' => 'zero_distance_gap', 'route_id' => $s->route_id,
                    'segment_id' => $s->seg_id,
                    'detail' => "{$s->route_name}: {$s->from_n} -> {$s->to_n}",
                ];
            }
        }
    }

    private function checkDisconnectedChains(): void
    {
        $this->newLine();
        $this->info('# 3. DISCONNECTED CHAINS (prev.to != next.from)');
        $this->line(str_repeat('-', 55));

        $routeIds = DB::table('routes')->pluck('id');
        $found = 0;

        foreach ($routeIds as $routeId) {
            $segs = DB::table('route_segments as rs')
                ->leftJoin('waypoints as fw', 'rs.from_waypoint_id', '=', 'fw.id')
                ->leftJoin('waypoints as tw', 'rs.to_waypoint_id', '=', 'tw.id')
                ->where('rs.route_id', $routeId)
                ->orderBy('rs.sequence')
                ->select('rs.sequence', 'rs.to_waypoint_id', 'rs.from_waypoint_id',
                         'tw.name as to_n', 'fw.name as from_n')
                ->get();

            if ($segs->count() < 2) continue;

            for ($i = 1; $i < $segs->count(); $i++) {
                $prev = $segs[$i - 1];
                $curr = $segs[$i];
                if ($prev->to_waypoint_id !== $curr->from_waypoint_id) {
                    $routeName = DB::table('routes')->where('id', $routeId)->value('name') ?? '?';
                    $this->warn("Route #{$routeId} {$routeName}: seq {$prev->sequence} ends at '{$prev->to_n}' but seq {$curr->sequence} starts at '{$curr->from_n}'");
                    $this->problems[] = [
                        'type' => 'chain_gap', 'route_id' => $routeId, 'segment_id' => '',
                        'detail' => "{$routeName}: {$prev->to_n} != {$curr->from_n}",
                    ];
                    $found++;
                }
            }
        }
        if ($found === 0) $this->info('All route chains continuous.');
    }

    private function checkDuplicateSegments(): void
    {
        $this->newLine();
        $this->info('# 4. DUPLICATE SEGMENTS IN SAME ROUTE');
        $this->line(str_repeat('-', 55));

        $dups = DB::table('route_segments')
            ->select('route_id', 'from_waypoint_id', 'to_waypoint_id', DB::raw('COUNT(*) as c'))
            ->groupBy('route_id', 'from_waypoint_id', 'to_waypoint_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($dups->isEmpty()) { $this->info('None.'); return; }

        foreach ($dups as $d) {
            $rn = DB::table('routes')->where('id', $d->route_id)->value('name') ?? '?';
            $fn = DB::table('waypoints')->where('id', $d->from_waypoint_id)->value('name') ?? '?';
            $tn = DB::table('waypoints')->where('id', $d->to_waypoint_id)->value('name') ?? '?';
            $this->warn("Route {$rn}: {$fn} -> {$tn} appears {$d->c} times");
            $this->problems[] = [
                'type' => 'duplicate_segment', 'route_id' => $d->route_id, 'segment_id' => '',
                'detail' => "{$rn}: {$fn} -> {$tn} x{$d->c}",
            ];
        }
    }

    private function checkKnownDetours(): void
    {
        $this->newLine();
        $this->info('# 5. KNOWN DETOUR WAYPOINTS (famous treks)');
        $this->line(str_repeat('-', 55));

        $routes = DB::table('routes')->get();
        $matched = 0;

        foreach ($routes as $r) {
            $nameLower = strtolower($r->name);
            foreach ($this->knownDetours as $pattern => $requiredWaypoints) {
                if (str_contains($nameLower, $pattern)) {
                    $matched++;

                    $segs = DB::table('route_segments as rs')
                        ->join('waypoints as fw', 'rs.from_waypoint_id', '=', 'fw.id')
                        ->join('waypoints as tw', 'rs.to_waypoint_id', '=', 'tw.id')
                        ->where('rs.route_id', $r->id)
                        ->select('fw.name as fn', 'tw.name as tn')
                        ->get();

                    $allNames = '';
                    foreach ($segs as $s) {
                        $allNames .= ' ' . strtolower((string) $s->fn) . ' ' . strtolower((string) $s->tn);
                    }

                    foreach ($requiredWaypoints as $req) {
                        if (str_contains($allNames, $req)) {
                            $this->info("Route #{$r->id} {$r->name}: '{$req}' PRESENT");
                        } else {
                            $this->error("Route #{$r->id} {$r->name}: '{$req}' MISSING");
                            $this->problems[] = [
                                'type' => 'missing_detour', 'route_id' => $r->id, 'segment_id' => '',
                                'detail' => "{$r->name} missing '{$req}'",
                            ];
                        }
                    }
                }
            }
        }
        if ($matched === 0) $this->warn('No routes matched known-detour patterns.');
    }

    private function dumpAbcDetail(): void
    {
        $this->newLine();
        $this->info('# 6. ABC DETAIL DUMP (manual review)');
        $this->line(str_repeat('-', 55));

        try {
            $planner = app(PlannerService::class);
            $route = $planner->resolveRouteForProvider('Annapurna Base Camp Trek');
            if (!$route) { $this->error('ABC route not resolved'); return; }

            $this->line("Route #{$route->id}: {$route->name}");
            $segs = $route->segments()->with(['fromWaypoint', 'toWaypoint'])->orderBy('sequence')->get();
            foreach ($segs as $i => $s) {
                $this->line(sprintf(
                    '%2d. %-25s -> %-25s  %skm / %sh',
                    $i + 1,
                    $s->fromWaypoint->name ?? '?',
                    $s->toWaypoint->name ?? '?',
                    $s->distance_km ?? '?',
                    $s->estimated_time_hours ?? '?'
                ));
            }
        } catch (\Throwable $e) {
            $this->error('ABC dump error: ' . $e->getMessage());
        }
    }

    private function exportCsv(): void
    {
        $path = base_path((string) ($this->option('csv') ?: 'storage/logs/route-quality-report.csv'));
        File::ensureDirectoryExists(dirname($path));
        $fh = fopen($path, 'w');
        fputcsv($fh, ['type', 'route_id', 'segment_id', 'detail']);
        foreach ($this->problems as $p) {
            fputcsv($fh, [$p['type'] ?? '', $p['route_id'] ?? '', $p['segment_id'] ?? '', $p['detail'] ?? '']);
        }
        fclose($fh);
        $this->newLine();
        $this->info("CSV: {$path}");
    }

    private function summary(): void
    {
        $this->newLine();
        $this->info('====================================================');
        $this->info('  SUMMARY');
        $this->info('====================================================');

        if (empty($this->problems)) {
            $this->info('No structural problems detected.');
            return;
        }
        $byType = [];
        foreach ($this->problems as $p) {
            $byType[$p['type']] = ($byType[$p['type']] ?? 0) + 1;
        }
        arsort($byType);
        foreach ($byType as $t => $c) {
            $this->error(sprintf('  %-30s %d', $t, $c));
        }
        $this->line('Total problems: ' . count($this->problems));
    }
}