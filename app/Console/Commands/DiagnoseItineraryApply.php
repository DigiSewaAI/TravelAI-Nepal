<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class DiagnoseItineraryApply extends Command
{
    protected $signature = 'diagnose:itinerary-apply
                            {--service= : Service ID or name}
                            {--log-lines=500}';

    protected $description = 'Read-only diagnostic for "Apply to Itinerary" bug';

    private array $findings = [];

    public function handle(): int
    {
        $this->newLine();
        $this->info('===================================================');
        $this->info('  TravelAI - Itinerary Apply Diagnostics');
        $this->info('===================================================');

        $this->checkRoutes();
        $this->checkMorphMap();
        $this->checkConfigCache();
        $this->checkServiceAndItinerary();
        $this->checkSchema();
        $this->testInsert();
        $this->checkLogs();

        $this->summarize();

        return self::SUCCESS;
    }

    // renamed to avoid collision with base Command::warn()
    private function header(string $t): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold># {$t}</>");
        $this->line(str_repeat('-', 58));
    }

    private function reportOk(string $m): void
    {
        $this->line("  [OK]   {$m}");
    }

    private function reportWarn(string $m, string $tag = ''): void
    {
        $this->line("  [WARN] {$m}" . ($tag ? " [{$tag}]" : ''));
        $this->findings[] = ['warn', $m];
    }

    private function reportFail(string $m, string $tag = ''): void
    {
        $this->line("  [FAIL] {$m}" . ($tag ? " [{$tag}]" : ''));
        $this->findings[] = ['fail', $m];
    }

    private function sub(string $m): void
    {
        $this->line("         {$m}");
    }

    // ---- 1. ROUTES -------------------------------------------------
    private function checkRoutes(): void
    {
        $this->header('1. ITINERARY ROUTES');

        $routes = collect(Route::getRoutes())->filter(fn ($r) =>
            str_contains($r->uri(), 'itinerar') ||
            str_contains((string) $r->getName(), 'itinerar')
        );

        if ($routes->isEmpty()) {
            $this->reportFail('No itinerary routes registered', 'ROUTE_MISSING');
            return;
        }

        $applyRoutes = $routes->filter(fn ($r) =>
            str_contains($r->uri(), 'apply') ||
            str_contains((string) $r->getName(), 'apply')
        );

        if ($applyRoutes->isEmpty()) {
            $this->reportFail('No "apply" route found - frontend posting to wrong URL', 'APPLY_ROUTE_MISSING');
        }

        foreach ($routes->sortBy(fn ($r) => $r->uri()) as $route) {
            $methods   = implode('|', array_diff($route->methods(), ['HEAD']));
            $isApply   = str_contains($route->uri(), 'apply')
                      || str_contains((string) $route->getName(), 'apply');
            $prefix    = $isApply ? '>>APPLY<<' : '         ';

            $this->line("  {$prefix} {$methods} /{$route->uri()}");
            $this->sub("name: " . ($route->getName() ?? '-'));
            $this->sub("action: " . $route->getActionName());

            $mw = implode(',', $route->gatherMiddleware());
            $this->sub("middleware: {$mw}");

            if ($isApply) {
                if (preg_match('/(verified|kyc|can:|subscribed|payment)/i', $mw)) {
                    $this->reportWarn('Apply route gated by ' . $mw . ' - unverified = silent 302/403', 'MIDDLEWARE_GATE');
                }
                if (str_contains($route->getActionName(), 'Closure')) {
                    $this->reportWarn('Apply route is Closure - logic not in controller', 'CLOSURE_ROUTE');
                }
            }
            $this->newLine();
        }
    }

    // ---- 2. MORPH MAP ----------------------------------------------
    private function checkMorphMap(): void
    {
        $this->header('2. POLYMORPHIC MORPH MAP');
        try {
            $map = Relation::morphMap();
        } catch (\Throwable $e) {
            $this->reportWarn('Cannot read morphMap: ' . $e->getMessage());
            return;
        }

        if (empty($map)) {
            $this->reportWarn('morphMap is EMPTY - full class names stored', 'MORPH_NONE');
            return;
        }

        foreach ($map as $alias => $class) {
            $exists = class_exists($class) ? 'OK' : '!! class missing';
            $this->line("  {$alias} => {$class}  ({$exists})");
        }
    }

    // ---- 3. CACHE STATE --------------------------------------------
    private function checkConfigCache(): void
    {
        $this->header('3. DEPLOY / CACHE STATE');
        $c = [
            'config' => base_path('bootstrap/cache/config.php'),
            'routes' => base_path('bootstrap/cache/routes-v7.php'),
        ];
        foreach ($c as $k => $p) {
            file_exists($p)
                ? $this->line("  * {$k} cache present")
                : $this->line("  - {$k} cache absent");
        }
        $this->sub('If just deployed: php artisan optimize:clear');
    }

    // ---- 4. SERVICE + ITINERARY DATA -------------------------------
    private function checkServiceAndItinerary(): void
    {
        $this->header('4. SERVICE + ITINERARY DATA');

        if (!Schema::hasTable('services')) {
            $this->reportFail('services table missing', 'NO_SERVICES_TABLE');
            return;
        }

        $q   = DB::table('services');
        $opt = $this->option('service');
        if ($opt) {
            $q->where(fn ($w) => is_numeric($opt)
                ? $w->where('id', (int) $opt)
                : $w->where('name', 'like', "%{$opt}%"));
        }

        $services = $q->limit(10)->get();
        if ($services->isEmpty()) {
            $this->reportFail('No service matched filter', 'SERVICE_NOT_FOUND');
            return;
        }

        foreach ($services as $svc) {
            $this->line("  Service #{$svc->id}: {$svc->name}");
            foreach (['category', 'status', 'serviceable_type', 'serviceable_id', 'vertical', 'vertical_id', 'type'] as $col) {
                if (Schema::hasColumn('services', $col)) {
                    $this->sub("{$col}: " . ($svc->{$col} ?? 'NULL'));
                }
            }

            if (Schema::hasColumn('services', 'serviceable_type') && !empty($svc->serviceable_type)) {
                $map   = Relation::morphMap();
                $val   = $svc->serviceable_type;
                $valid = in_array($val, array_keys($map), true)
                      || in_array($val, array_values($map), true)
                      || class_exists($val);
                $valid
                    ? $this->reportOk("serviceable_type '{$val}' resolves OK")
                    : $this->reportFail("serviceable_type '{$val}' NOT in morphMap & class missing", 'MORPH_MISMATCH');
            }

            if (Schema::hasTable('itineraries')) {
                $cols = Schema::getColumnListing('itineraries');
                $fk   = in_array('service_id', $cols) ? 'service_id'
                      : (in_array('serviceable_id', $cols) ? 'serviceable_id' : null);

                if ($fk) {
                    $itos = DB::table('itineraries')->where($fk, $svc->id)->get();
                    $this->sub("itineraries for service: {$itos->count()}");

                    foreach ($itos as $ito) {
                        $days = Schema::hasTable('itinerary_days')
                            ? DB::table('itinerary_days')->where('itinerary_id', $ito->id)->count()
                            : 0;
                        $state = $ito->status ?? $ito->state ?? '-';
                        $this->sub("  -> itinerary #{$ito->id} status={$state} days={$days}");

                        if ($days === 0) {
                            $this->reportWarn("Itinerary #{$ito->id} exists with 0 days - apply inserted header, failed on days", 'EMPTY_ITINERARY');
                        }
                    }
                    if ($itos->isEmpty()) {
                        $this->reportWarn("No itinerary row at all for service #{$svc->id}", 'NO_ITINERARY_ROW');
                    }
                }
            }
            $this->newLine();
        }

        if (Schema::hasTable('itineraries') && Schema::hasColumn('itineraries', 'service_id')) {
            $orphans = DB::table('itineraries')
                ->leftJoin('services', 'itineraries.service_id', '=', 'services.id')
                ->whereNull('services.id')->count();
            $orphans > 0 && $this->reportWarn("{$orphans} orphaned itineraries (broken service_id)", 'ORPHAN_ITINERARIES');
        }

        if (Schema::hasTable('itineraries')) {
            $recent = DB::table('itineraries')->orderByDesc('id')->limit(5)->get();
            if ($recent->isNotEmpty()) {
                $this->line('  Last 5 itinerary rows (all services):');
                foreach ($recent as $r) {
                    $this->sub("#{$r->id}  service_id=" . ($r->service_id ?? '-')
                        . "  created_at=" . ($r->created_at ?? '-'));
                }
            }
        }
    }

    // ---- 5. SCHEMA RISKS -------------------------------------------
    private function checkSchema(): void
    {
        $this->header('5. SCHEMA RISKS (NOT NULL without default)');

        $driver = DB::getDriverName();

        foreach (['itineraries', 'itinerary_days'] as $tbl) {
            if (!Schema::hasTable($tbl)) {
                $this->reportFail("Table {$tbl} missing", 'NO_TABLE');
                continue;
            }
            $this->line("  {$tbl}");

            if ($driver === 'mysql') {
                $cols = DB::select("SHOW COLUMNS FROM `{$tbl}`");
                foreach ($cols as $c) {
                    if ($c->Null === 'NO'
                        && $c->Default === null
                        && !in_array($c->Field, ['id', 'created_at', 'updated_at'], true)
                        && stripos($c->Extra, 'auto_increment') === false
                    ) {
                        $this->sub("NOT NULL no-default: {$c->Field} ({$c->Type})");
                        $this->findings[] = ['warn', "{$tbl}.{$c->Field} NOT NULL no-default"];
                    }
                }
            } else {
                $cols = DB::select(
                    "SELECT column_name, is_nullable, column_default
                     FROM information_schema.columns
                     WHERE table_name = ?", [$tbl]
                );
                foreach ($cols as $c) {
                    if ($c->is_nullable === 'NO'
                        && $c->column_default === null
                        && !in_array($c->column_name, ['id', 'created_at', 'updated_at'], true)
                    ) {
                        $this->sub("NOT NULL no-default: {$c->column_name}");
                        $this->findings[] = ['warn', "{$tbl}.{$c->column_name} NOT NULL no-default"];
                    }
                }
            }
        }
    }

    // ---- 6. INSERT DRY-RUN -----------------------------------------
    private function testInsert(): void
    {
        $this->header('6. INSERT DRY-RUN (rolled back - safe)');

        if (!Schema::hasTable('itineraries')) {
            return;
        }

        $svcId = (int) ($this->option('service') ?: DB::table('services')->value('id'));
        if (!$svcId) {
            $this->sub('no service to test against');
            return;
        }

        DB::beginTransaction();
        try {
            $itoCols = Schema::getColumnListing('itineraries');
            $ito     = ['created_at' => now(), 'updated_at' => now()];
            if (in_array('service_id', $itoCols)) {
                $ito['service_id'] = $svcId;
            }
            foreach (['status', 'state'] as $c) {
                if (in_array($c, $itoCols)) {
                    $ito[$c] = 'draft';
                    break;
                }
            }

            $itoId = DB::table('itineraries')->insertGetId($ito);
            $this->reportOk("Header insert OK -> itinerary_id={$itoId}");

            if (Schema::hasTable('itinerary_days')) {
                $dayCols = Schema::getColumnListing('itinerary_days');
                $day     = ['itinerary_id' => $itoId, 'created_at' => now(), 'updated_at' => now()];
                foreach (['day_number', 'day', 'order', 'position'] as $c) {
                    if (in_array($c, $dayCols)) {
                        $day[$c] = 1;
                        break;
                    }
                }
                foreach (['title', 'name', 'heading'] as $c) {
                    if (in_array($c, $dayCols)) {
                        $day[$c] = 'DRY-RUN';
                        break;
                    }
                }
                DB::table('itinerary_days')->insert($day);
                $this->reportOk('Day insert OK');
            }
            $this->line('  ==> Schema & constraints OK. Bug is in application logic (validation / payload mapping / save flow).');
        } catch (\Throwable $e) {
            $this->reportFail('Insert FAILED: ' . get_class($e), 'INSERT_FAILED');
            $this->sub(substr($e->getMessage(), 0, 400));
        } finally {
            DB::rollBack();
        }
    }

    // ---- 7. LOG TAIL (Windows-safe) --------------------------------
    private function checkLogs(): void
    {
        $this->header('7. RECENT ERRORS IN LOG');

        $path = storage_path('logs/laravel.log');
        if (!file_exists($path)) {
            $this->sub('no log file');
            return;
        }

        $n     = (int) $this->option('log-lines');
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            $this->sub('log empty');
            return;
        }
        $tail = array_slice($lines, -$n);

        $hits = [];
        foreach ($tail as $line) {
            if (preg_match('/itinerar|SQLSTATE|Call to undefined|Allowed memory|Unauthenticated|Forbidden|419|CSRF|MassAssignment|QueryException/i', $line)) {
                $hits[] = $line;
            }
        }
        if (empty($hits)) {
            $this->reportOk("No keywords in last {$n} lines");
        } else {
            foreach (array_slice($hits, -30) as $h) {
                $this->line('  ' . substr($h, 0, 260));
            }
        }
    }

    // ---- SUMMARY ---------------------------------------------------
    private function summarize(): void
    {
        $this->newLine();
        $this->info('===================================================');
        $this->info('  SUMMARY');
        $this->info('===================================================');

        $fails = array_filter($this->findings, fn ($f) => $f[0] === 'fail');
        $warns = array_filter($this->findings, fn ($f) => $f[0] === 'warn');

        if (empty($this->findings)) {
            $this->reportOk('No backend red flags - inspect browser Network tab (payload/status) & JS handler');
            return;
        }
        if ($fails) {
            $this->error('FAILURES:');
            foreach ($fails as $f) {
                $this->line('  [FAIL] ' . $f[1]);
            }
        }
        if ($warns) {
            $this->warn('WARNINGS:');  // base Command::warn() - safe to call
            foreach ($warns as $f) {
                $this->line('  [WARN] ' . $f[1]);
            }
        }
    }
}