# TravelAI Nepal — MASTER FIX SPECIFICATION v1.5

**Status:** FINAL — Ready for Master final approval
**Based on:** v1.4 + Master Review #4 (6 corrections)
**This version:** Resolves all 6 remaining corrections. No new redesigns.
**Sections unchanged from v1.4:** A0, A1, A4, A5, A6, A8, A9, B0, B1.1–B1.4, B1.6–B1.8, C, D, F, H, I, K, L, M, N, P, T (existing tests), U.

**⚠️ THIS IS THE FINAL SPECIFICATION. Once approved, implementation begins.**

---

## 📋 MASTER REVIEW #4 — ALL 6 CORRECTIONS ADDRESSED

| # | Correction | Section Updated |
|---|---|---|
| 1 | Migration — `hasColumn()` outside Blueprint closure | § A3.2 |
| 2 | Webhook — claim ownership / lease semantics | § G2 |
| 3 | A7 — verification vs signed URL contradiction | § A7 |
| 4 | A3 crash trade-off — documented | § A3.6 |
| 5 | G3 — payment/product decision (Master choice) | § G3 |
| 6 | FIX-03 vs FIX-06 boundary clarified | § O |

---

# SECTION A — REVISED SECTIONS ONLY

## A3.2 — Migration Fix (Correction #1)

**`Schema::hasColumn()` MUST be called outside the `Blueprint` closure.**

### A3.2.1 Extend `ai_usage` — CORRECTED

```php
// database/migrations/2026_09_15_XXXXXX_extend_ai_usage_for_reservation.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pre-flight: table must exist
        if (!Schema::hasTable('ai_usage')) {
            throw new \RuntimeException(
                'ai_usage table missing — cannot extend. Aborting migration.'
            );
        }

        // Pre-flight: legacy `count` column must exist (used for backfill)
        if (!Schema::hasColumn('ai_usage', 'count')) {
            throw new \RuntimeException(
                'ai_usage.count column missing — schema drift detected. Aborting.'
            );
        }

        // Add reserved if absent
        if (!Schema::hasColumn('ai_usage', 'reserved')) {
            Schema::table('ai_usage', function (Blueprint $table) {
                $table->integer('reserved')->default(0)->after('count');
            });
        }

        // Add finalized if absent
        if (!Schema::hasColumn('ai_usage', 'finalized')) {
            Schema::table('ai_usage', function (Blueprint $table) {
                $table->integer('finalized')->default(0)->after('reserved');
            });
        }

        // Migrate legacy data: count → finalized (one-time)
        DB::table('ai_usage')
            ->where('finalized', 0)
            ->where('reserved', 0)
            ->update(['finalized' => DB::raw('`count`')]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('ai_usage')) {
            return;
        }

        // Restore `count` from `finalized` for legacy compatibility
        if (Schema::hasColumn('ai_usage', 'finalized')) {
            DB::table('ai_usage')->update(['count' => DB::raw('finalized')]);
        }

        if (Schema::hasColumn('ai_usage', 'reserved')) {
            Schema::table('ai_usage', function (Blueprint $table) {
                $table->dropColumn('reserved');
            });
        }

        if (Schema::hasColumn('ai_usage', 'finalized')) {
            Schema::table('ai_usage', function (Blueprint $table) {
                $table->dropColumn('finalized');
            });
        }
    }
};