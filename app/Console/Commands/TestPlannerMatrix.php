<?php

namespace App\Console\Commands;

use App\Models\Waypoint;
use App\Services\PlannerService;
use Illuminate\Console\Command;

class TestPlannerMatrix extends Command
{
    protected $signature = 'planner:test-matrix {--cleanup : Delete generated test records after run}';
    protected $description = 'Run full planner test matrix across 20 routes to validate Phase 4 fixes';

    public function handle(PlannerService $planner)
    {
        $routes = [
            ['slug' => 'annapurna-circuit',    'days' => 15, 'budget' => 1500],
            ['slug' => 'api-himal',            'days' => 15, 'budget' => 1500],
            ['slug' => 'kanchenjunga-north',   'days' => 18, 'budget' => 2000],
            ['slug' => 'kanchenjunga-south',   'days' => 18, 'budget' => 2000],
            ['slug' => 'kanchenjunga-circuit', 'days' => 23, 'budget' => 2500],
            ['slug' => 'bardiya-trek',         'days' => 7,  'budget' => 500],
            ['slug' => 'everest-base-camp',    'days' => 14, 'budget' => 2000],
            ['slug' => 'makalu-base-camp',     'days' => 17, 'budget' => 2000],
            ['slug' => 'makalu-barun',         'days' => 19, 'budget' => 2500],
            ['slug' => 'manaslu-circuit',      'days' => 15, 'budget' => 2000],
            ['slug' => 'upper-dolpo',          'days' => 18, 'budget' => 2500],
            ['slug' => 'dolpo-circuit',        'days' => 20, 'budget' => 3000],
            ['slug' => 'phoksundo-lake',       'days' => 11, 'budget' => 1200],
            ['slug' => 'damodar-kunda',        'days' => 14, 'budget' => 2000],
            ['slug' => 'humla',                'days' => 15, 'budget' => 2000],
            ['slug' => 'rolwaling',            'days' => 13, 'budget' => 1500],
            ['slug' => 'saipal',               'days' => 13, 'budget' => 1500],
            ['slug' => 'langtang-valley',      'days' => 7,  'budget' => 1000],
            ['slug' => 'gokyo-lakes',          'days' => 12, 'budget' => 1800],
            ['slug' => 'three-passes',         'days' => 18, 'budget' => 2500],
        ];

        $this->info('🔍 Running planner test matrix...');
        $this->newLine();

        $results = [];
        $generatedIds = [];

        foreach ($routes as $test) {
            $this->line("Testing: {$test['slug']} ({$test['days']}d)");

            try {
                $result = $planner->generate([
                    'destination'  => $test['slug'],
                    'days'         => $test['days'],
                    'budget'       => $test['budget'],
                    'travel_style' => 'mid_range',
                ], 'en');

                $days     = $result['days'];
                $metadata = $result['metadata'] ?? [];
                $breakdown = $result['breakdown'] ?? [];

                $generatedIds[] = $result['request']->id ?? null;

                $dayCount       = count($days);
                $routeDataDays  = $metadata['route_data_days'] ?? null;
                $sufficiency    = $metadata['data_sufficiency'] ?? 'unknown';

                $fakeDays = $days->filter(function ($d) {
                    $t = $d->title ?? '';
                    return stripos($t, 'No Itinerary') !== false
                        || stripos($t, 'Buffer Day') !== false
                        || stripos($t, 'यात्रा डेटा छैन') !== false
                        || stripos($t, '无行程') !== false;
                })->count();

                $passOvernight = 0;
                foreach ($days as $day) {
                    if ($day->overnight_waypoint_id) {
                        $wp = Waypoint::find($day->overnight_waypoint_id);
                        if ($wp && in_array($wp->type, ['pass', 'peak', 'lake'])) {
                            $passOvernight++;
                        }
                    }
                }

                $totalCost  = $result['total_cost'] ?? 0;
                $costValid  = $totalCost > 0;

                $metaOk = isset($metadata['route_data_days'])
                       && isset($metadata['requested_days'])
                       && isset($metadata['data_sufficiency']);

                $wording = 'n/a';
                if (isset($breakdown['budget_insufficient']['message'])) {
                    $wording = str_contains($breakdown['budget_insufficient']['message'], 'Estimated cost is')
                        ? 'ok' : '❌ reversed';
                }

                // ============================================================
                // Phase 4N.1d: 3-tier status (PASS / WARN / FAIL)
                // ============================================================
                if ($fakeDays > 0 || $passOvernight > 0 || !$costValid || !$metaOk) {
                    $status = '❌ FAIL';
                    $statusNote = 'fake/passO/cost/meta';
                } elseif ($sufficiency === 'insufficient') {
                    $status = '⚠️ WARN';
                    $statusNote = "data: {$routeDataDays}/{$test['days']}";
                } else {
                    $status = '✅ PASS';
                    $statusNote = 'complete';
                }

                $results[] = [
                    'route'    => $test['slug'],
                    'req'      => $test['days'],
                    'ret'      => $dayCount,
                    'data'     => $routeDataDays ?? '-',
                    'suff'     => substr($sufficiency, 0, 4),
                    'fake'     => $fakeDays,
                    'pass_o'   => $passOvernight,
                    'cost'     => number_format($totalCost, 0),
                    'word'     => $wording,
                    'status'   => $status,
                    'note'     => $statusNote,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'route'  => $test['slug'],
                    'req'    => $test['days'],
                    'ret'    => '-',
                    'data'   => '-',
                    'suff'   => '-',
                    'fake'   => '-',
                    'pass_o' => '-',
                    'cost'   => '-',
                    'word'   => '-',
                    'status' => '❌ FAIL',
                    'note'   => 'EXC: ' . substr($e->getMessage(), 0, 40),
                ];
            }
        }

        $this->newLine();
        $this->table(
            ['Route', 'Req', 'Ret', 'Data', 'Suff', 'Fake', 'PassO', 'Cost', 'Word', 'Status', 'Note'],
            array_map(fn($r) => [
                $r['route'], $r['req'], $r['ret'], $r['data'],
                $r['suff'], $r['fake'], $r['pass_o'], $r['cost'],
                $r['word'], $r['status'], $r['note'],
            ], $results)
        );

        // ============================================================
        // Phase 4N.1d: 3-tier summary
        // ============================================================
        $passed = collect($results)->where('status', '✅ PASS')->count();
        $warned = collect($results)->where('status', '⚠️ WARN')->count();
        $failed = collect($results)->where('status', '❌ FAIL')->count();

        $this->newLine();
        $this->info('📊 Results:');
        $this->line("   ✅ PASS: {$passed}");
        $this->line("   ⚠️ WARN: {$warned} (insufficient data — no fake padding)");
        $this->line("   ❌ FAIL: {$failed}");

        if ($this->option('cleanup') && !empty($generatedIds)) {
            $deleted = \App\Models\PlannerRequest::whereIn('id', array_filter($generatedIds))->delete();
            $this->line("🧹 Cleaned up {$deleted} test planner requests");
        }

        return $failed === 0 ? 0 : 1;
    }
}