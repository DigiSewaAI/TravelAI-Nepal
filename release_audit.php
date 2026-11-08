<?php
/**
 * RELEASE AUDIT — READ-ONLY
 * कोई code change, कोई DB write, कोई commit नै
 * 
 * Focus: Claim Systems push safety
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "═══════════════════════════════════════════════════════\n";
echo "  RELEASE AUDIT — CLAIM SYSTEMS\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════
// [1] Git State
// ═══════════════════════════════════════════════════════
echo "─── [1] Git State ───\n";
echo "  Branch: " . trim(shell_exec('git branch --show-current 2>&1')) . "\n";
echo "  HEAD: " . trim(shell_exec('git log -1 --oneline 2>&1')) . "\n";
echo "  Commits ahead: ";
$status = trim(shell_exec('git status --short 2>&1'));
$logCount = trim(shell_exec('git rev-list --count origin/main..HEAD 2>&1'));
echo "{$logCount}\n\n";

echo "  Uncommitted changes:\n";
if (empty($status)) {
    echo "    (none)\n";
} else {
    echo "    " . str_replace("\n", "\n    ", $status) . "\n";
}

// ═══════════════════════════════════════════════════════
// [2] New Migrations — What tables/columns?
// ═══════════════════════════════════════════════════════
echo "\n─── [2] New Migrations (since last push) ───\n";
$lastPushedCommit = trim(shell_exec('git rev-parse origin/main 2>&1'));
$newMigrations = trim(shell_exec('git diff --name-only ' . $lastPushedCommit . '..HEAD -- database/migrations/ 2>&1'));

if (empty($newMigrations)) {
    echo "  ❌ कोई नयाँ migration नै\n";
} else {
    foreach (explode("\n", $newMigrations) as $m) {
        $m = trim($m);
        if (empty($m)) continue;
        echo "\n  📄 " . basename($m) . "\n";
        
        if (file_exists($m)) {
            $content = file_get_contents($m);
            
            // Detect: additive vs destructive
            $isAdditive = !preg_match('/dropColumn|dropTable|truncate|delete\s+from|migrate:fresh/i', $content);
            $hasSchemaTable = str_contains($content, 'Schema::table');
            $hasSchemaCreate = str_contains($content, 'Schema::create');
            
            echo "    Additive only: " . ($isAdditive ? '✅ YES' : '🔴 NO — contains destructive ops') . "\n";
            echo "    Schema::table: " . ($hasSchemaTable ? '✅' : '❌') . "\n";
            echo "    Schema::create: " . ($hasSchemaCreate ? '✅' : '❌') . "\n";
            
            // Show operations
            if (preg_match_all('/\$table->(\w+)\(([^)]+)\)/', $content, $matches, PREG_SET_ORDER)) {
                echo "    Operations:\n";
                foreach ($matches as $match) {
                    echo "      • {$match[1]}({$match[2]})\n";
                }
            }
        }
    }
}

// ═══════════════════════════════════════════════════════
// [3] Migration Status — Local vs Expected
// ═══════════════════════════════════════════════════════
echo "\n─── [3] Migration Batch Status ───\n";
$pending = DB::table('migrations')->orderBy('id', 'desc')->limit(5)->get();
echo "  Latest 5 migrations:\n";
foreach ($pending as $m) {
    echo "    Batch {$m->batch}: {$m->migration}\n";
}

// ═══════════════════════════════════════════════════════
// [4] Config/Env — CLAIM_CODE_SECRET required
// ═══════════════════════════════════════════════════════
echo "\n─── [4] Config Check ───\n";
$claimSecret = config('claim.code_secret');
echo "  CLAIM_CODE_SECRET: " . ($claimSecret ? '✅ SET (' . strlen($claimSecret) . ' chars)' : '❌ MISSING') . "\n";
echo "  Feature flags:\n";
foreach (['restaurant_enabled', 'transport_enabled', 'hotel_enabled', 'artcraft_enabled', 'moneychanger_enabled', 'individual_enabled'] as $flag) {
    $val = config("claim.{$flag}");
    echo "    claim.{$flag}: " . ($val ? '✅ ON' : '⏸️ OFF') . "\n";
}

// ═══════════════════════════════════════════════════════
// [5] New Files — What's being added?
// ═══════════════════════════════════════════════════════
echo "\n─── [5] New Files (since origin/main) ───\n";
$newFiles = trim(shell_exec('git diff --name-status ' . $lastPushedCommit . '..HEAD 2>&1'));
$added = 0;
$modified = 0;
$deleted = 0;
$newFilesList = [];

foreach (explode("\n", $newFiles) as $line) {
    if (preg_match('/^A\s+(.+)$/', $line, $m)) {
        $added++;
        $newFilesList[] = "ADDED: {$m[1]}";
    } elseif (preg_match('/^M\s+(.+)$/', $line, $m)) {
        $modified++;
    } elseif (preg_match('/^D\s+(.+)$/', $line, $m)) {
        $deleted++;
    }
}
echo "  Added: {$added}, Modified: {$modified}, Deleted: {$deleted}\n";
echo "\n  Added files (first 20):\n";
foreach (array_slice($newFilesList, 0, 20) as $f) {
    echo "    {$f}\n";
}
if (count($newFilesList) > 20) {
    echo "    ... +" . (count($newFilesList) - 20) . " more\n";
}

// ═══════════════════════════════════════════════════════
// [6] Tests Status
// ═══════════════════════════════════════════════════════
echo "\n─── [6] Tests (running — please wait) ───\n";
$testOutput = shell_exec('php artisan test --stop-on-failure 2>&1 | findstr /C:"Tests:" /C:"FAIL" 2>&1');
echo "  " . trim($testOutput) . "\n";

// ═══════════════════════════════════════════════════════
// [7] Database Backup Exists?
// ═══════════════════════════════════════════════════════
echo "\n─── [7] Backup Files ───\n";
$backups = glob('backup_*.sql');
if (empty($backups)) {
    echo "  ⚠️ कोई backup file नै — push गर्नु अघि backup लिनुहोस्!\n";
} else {
    foreach (array_slice($backups, -5) as $b) {
        $size = round(filesize($b) / 1024 / 1024, 2);
        echo "  ✅ {$b} ({$size} MB)\n";
    }
}

// ═══════════════════════════════════════════════════════
// [8] Production-safe check: Any .env changes?
// ═══════════════════════════════════════════════════════
echo "\n─── [8] .env Changes in Git ───\n";
$envChanges = trim(shell_exec('git diff --name-only ' . $lastPushedCommit . '..HEAD -- .env* 2>&1'));
if (empty($envChanges)) {
    echo "  ✅ .env changes: NONE\n";
} else {
    echo "  ⚠️ .env changes: {$envChanges}\n";
    echo "  (Confirm production secrets मिल्नेछ)\n";
}

// ═══════════════════════════════════════════════════════
// [9] Critical: Any app/Config changes that need production env?
// ═══════════════════════════════════════════════════════
echo "\n─── [9] Production Env Required ───\n";
echo "  New ENV vars needed in production:\n";
$envFile = file_get_contents('.env.example');
foreach (['CLAIM_CODE_SECRET', 'RESTAURANT_CLAIM_ENABLED', 'TRANSPORT_CLAIM_ENABLED', 'HOTEL_CLAIM_ENABLED', 'ARTCRAFT_CLAIM_ENABLED', 'MONEYCHANGER_CLAIM_ENABLED', 'INDIVIDUAL_CLAIM_ENABLED'] as $env) {
    $hasInExample = str_contains($envFile, $env);
    echo "    {$env}: " . ($hasInExample ? '✅ documented' : '⚠️ not in .env.example') . "\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ AUDIT COMPLETE\n";
echo "═══════════════════════════════════════════════════════\n";