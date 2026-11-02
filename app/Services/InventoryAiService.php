<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * InventoryAiService — Stock Forecast + Reorder Assistant.
 * Deterministic-first + AI refine. Handles zero-data gracefully.
 */
class InventoryAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    /**
     * Stock Forecast — predict next $days demand per product.
     * Uses last 30 days order_items + current stock.
     */
    public function stockForecast(Provider $provider, int $days = 7): array
    {
        $reservation = null;
        try {
            $products = DB::table('products')->where('provider_id', $provider->id)->get();
            if ($products->isEmpty()) {
                return ['error' => 'no_products', 'message' => 'Add products first.'];
            }

            $stats = [];
            foreach ($products as $p) {
                $agg = DB::table('order_items')
                    ->where('provider_id', $provider->id)
                    ->where('product_id', $p->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->select(
                        DB::raw('COUNT(*) as orders'),
                        DB::raw('SUM(quantity) as units_sold')
                    )->first();

                $units = (int) ($agg->units_sold ?? 0);
                $avgPerDay = round($units / 30, 2);
                $stock = (int) ($p->stock_quantity ?? 0);
                $threshold = (int) ($p->low_stock_threshold ?? 3);

                // Deterministic: days of stock remaining at current velocity
                $daysLeft = $avgPerDay > 0 ? round($stock / ($units / 30), 1) : null;

                // Status
                // Deterministic status (NEVER overridden by AI)
                $status = 'healthy';
                if ($stock <= 0) {
                    $status = 'out_of_stock';
                } elseif ($stock <= $threshold) {
                    $status = 'low_stock';
                } elseif ($daysLeft !== null && $daysLeft < 7) {
                    $status = 'stockout_risk';
                } elseif ($daysLeft !== null && $daysLeft < 14) {
                    $status = 'restock_soon';
                }

                $stats[] = [
                    'name' => (string) $p->name,
                    'sku' => (string) $p->sku,
                    'product_type' => (string) $p->product_type,
                    'stock' => $stock,
                    'threshold' => $threshold,
                    'units_sold_30d' => $units,
                    'avg_per_day' => $avgPerDay,
                    'days_of_stock_left' => $daysLeft,
                    'status' => $status,
                ];
            }

            $totalSold = array_sum(array_column($stats, 'units_sold_30d'));
            $warn = $totalSold < 5
                ? "Very low sales data ({$totalSold} units in 30d) — forecast is baseline only."
                : null;

            $reservation = $this->aiLimit->reserve($provider, 'inventory.stock_forecast', ['days' => $days]);
            if (!$reservation) {
                return ['items' => $stats, 'summary' => 'Deterministic baseline (AI quota reached).', 'data_warning' => $warn, 'fallback' => true];
            }

            $prompt = "You are an inventory analyst. Today is " . now()->format('Y-m-d') . ".\n"
                . "Products with last-30d sales stats:\n" . json_encode($stats) . "\n\n"
                . "Forecast next {$days} days demand per product. For each product return:\n"
                . "  - name\n"
                . "  - predicted_units_next_{$days}d (int, based on avg_per_day × {$days})\n"
                . "  - risk (STRICT: out_of_stock ONLY if stock=0; low_stock if stock<=threshold; stockout_risk if stock>0 but <7 days left; restock_soon if <14 days; else ok)\n"
                . "  - reason (1 short sentence — quote actual units sold/avg/day)\n"
                . "Do NOT invent demand. If units_sold_30d=0, predicted=0.\n"
                . "Return JSON: {\"items\":[...],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2500, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['items'])) {
                $this->aiLimit->release($reservation);
                return ['items' => $stats, 'summary' => 'Deterministic baseline (AI parse failed).', 'data_warning' => $warn, 'fallback' => true];
            }

            $this->aiLimit->finalize($reservation);

            // Merge: AI provides reason only; deterministic status + predicted_units authoritative
            $aiBySku = [];
            foreach ($data['items'] as $ai) $aiBySku[(string)($ai['name'] ?? '')] = $ai;
            $merged = [];
            foreach ($stats as $s) {
                $ai = $aiBySku[$s['name']] ?? null;
                $predicted = (int) round($s['avg_per_day'] * $days);
                // AI reason only (if provided); deterministic fields always win
                $reason = $s['avg_per_day'] > 0
                    ? sprintf('%d units sold in 30d (%.2f/day) · %s days stock left', $s['units_sold_30d'], $s['avg_per_day'], $s['days_of_stock_left'] ?? '∞')
                    : 'No sales recorded in last 30 days';
                if ($ai && !empty($ai['reason'])) $reason = (string) $ai['reason'];
                $merged[] = [
                    'name' => $s['name'],
                    'sku' => $s['sku'],
                    'stock' => $s['stock'],
                    'units_sold_30d' => $s['units_sold_30d'],
                    'avg_per_day' => $s['avg_per_day'],
                    'days_of_stock_left' => $s['days_of_stock_left'],
                    'predicted_units_next_7d' => $predicted,
                    'status' => $s['status'],       // ← DETERMINISTIC WINS
                    'reason' => $reason,            // ← AI may refine wording
                ];
            }

            return [
                'items' => $merged,
                'summary' => (string) ($data['summary'] ?? 'Stock analysis ready.'),
                'data_warning' => $warn,
                'forecast_days' => $days,
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('InventoryAiService stockForecast failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Stock forecast temporarily unavailable.'];
        }
    }

    /**
     * Reorder Assistant — when + how much to reorder per product.
     * Deterministic: reorder_qty = (avg_daily × lead_days) - current_stock + safety_buffer
     */
    public function reorderAssistant(Provider $provider): array
    {
        $reservation = null;
        try {
            $products = DB::table('products')->where('provider_id', $provider->id)->get();
            if ($products->isEmpty()) {
                return ['error' => 'no_products', 'message' => 'Add products first.'];
            }

            $leadDays = 7; // assumption: 7-day supplier lead time
            $safetyDays = 3;
            $suggestions = [];

            foreach ($products as $p) {
                $agg = DB::table('order_items')
                    ->where('provider_id', $provider->id)
                    ->where('product_id', $p->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->select(DB::raw('SUM(quantity) as units_sold'))->first();
                $units = (int) ($agg->units_sold ?? 0);
                $avgPerDay = $units / 30;

                $stock = (int) ($p->stock_quantity ?? 0);
                $threshold = (int) ($p->low_stock_threshold ?? 3);

                // Reorder formula
                $needed = (int) ceil($avgPerDay * ($leadDays + $safetyDays));
                $reorderQty = max(0, $needed - $stock);
                if ($stock > $threshold && $avgPerDay < 0.1) $reorderQty = 0; // healthy + slow

                $urgency = 'ok';
                if ($stock <= 0) $urgency = 'critical';
                elseif ($stock <= $threshold) $urgency = 'high';
                elseif ($reorderQty > 0) $urgency = 'medium';

                $daysLeft = $avgPerDay > 0 ? round($stock / ($units / 30), 1) : null;
                $daysUntilReorder = $daysLeft !== null ? max(0, round($daysLeft - ($leadDays + $safetyDays), 1)) : null;
                if ($units === 0) {
                    $reason = 'No sales in 30d — hold current stock';
                } elseif ($reorderQty > 0) {
                    $reason = sprintf('Avg %.2f/day × %dd total (%dd lead + %dd safety) = need %d, stock %d → reorder %d now',
                        $avgPerDay, ($leadDays + $safetyDays), $leadDays, $safetyDays, $needed, $stock, $reorderQty);
                } else {
                    $reason = sprintf('Stock %d covers %.1f days at %.2f/day. Reorder in ~%.1f days to avoid stockout during %dd delivery (%dd lead + %dd safety).',
                        $stock, $daysLeft, $avgPerDay, $daysUntilReorder, ($leadDays + $safetyDays), $leadDays, $safetyDays);
                }

                $suggestions[] = [
                    'name' => (string) $p->name,
                    'sku' => (string) $p->sku,
                    'current_stock' => $stock,
                    'avg_per_day' => round($avgPerDay, 2),
                    'suggested_reorder_qty' => $reorderQty,
                    'days_until_reorder' => $daysUntilReorder,
                    'urgency' => $urgency,
                    'reason' => $reason,
                ];
            }

            // Sort by urgency
            usort($suggestions, fn($a, $b) => ['critical'=>0,'high'=>1,'medium'=>2,'ok'=>3][$a['urgency']] - ['critical'=>0,'high'=>1,'medium'=>2,'ok'=>3][$b['urgency']]);

            $totalReorderNeeded = array_sum(array_column($suggestions, 'suggested_reorder_qty'));
            $warn = $totalReorderNeeded === 0 ? 'All products at healthy stock level.' : null;

            $reservation = $this->aiLimit->reserve($provider, 'inventory.reorder_assistant', []);
            if (!$reservation) {
                return ['items' => $suggestions, 'summary' => 'Deterministic baseline (AI quota reached).', 'data_warning' => $warn, 'fallback' => true];
            }

            $prompt = "You are a warehouse manager. For each product below, confirm reorder urgency.\n"
                . "Rule: reorder_qty = avg_daily × (lead_days 7 + safety 3) - current_stock. Only suggest >0 if justified.\n"
                . "Products:\n" . json_encode($suggestions) . "\n\n"
                . "Return per product: name, urgency (critical|high|medium|ok), reason (1 short sentence, quote actual stock+velocity).\n"
                . "Do NOT force reorder if stock is healthy.\n"
                . "Return JSON: {\"items\":[...],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2500, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['items'])) {
                $this->aiLimit->release($reservation);
                return ['items' => $suggestions, 'summary' => 'Deterministic baseline (AI parse failed).', 'data_warning' => $warn, 'fallback' => true];
            }

            $this->aiLimit->finalize($reservation);

            // Merge AI reasons back into deterministic suggestions (keep quantities)
            $aiBySku = [];
            foreach ($data['items'] as $ai) $aiBySku[$ai['name'] ?? ''] = $ai;
            $merged = [];
            foreach ($suggestions as $s) {
                // Reason: DETERMINISTIC ONLY (AI hallucinates numbers like 7.5)
                $merged[] = [
                    'name' => $s['name'], 'sku' => $s['sku'],
                    'current_stock' => $s['current_stock'],
                    'suggested_reorder_qty' => $s['suggested_reorder_qty'],
                    'days_until_reorder' => $s['days_until_reorder'] ?? null,
                    'urgency' => $s['urgency'],
                    'reason' => $s['reason'],
                ];
            }

            // Deterministic summary (overrides AI, ensures Water Filter-type items mentioned)
            // Summary: status-based (restock_soon = monitor; low_stock/critical = immediate)
            $immediate = array_values(array_filter($merged, fn($x) => ($x['suggested_reorder_qty'] ?? 0) > 0));
            $monitor   = array_values(array_filter($merged, fn($x) => ($x['suggested_reorder_qty'] ?? 0) == 0
                && $x['current_stock'] > 0
                && ($x['days_until_reorder'] ?? 999) < 14));
            $healthyCount = count($merged) - count($immediate) - count($monitor);
            $summaryParts = [];
            if (!empty($immediate)) {
                $immediateStr = implode(', ', array_map(fn($x) => "{$x['name']} (reorder " . ($x['suggested_reorder_qty'] ?? 0) . " units)", $immediate));
                $summaryParts[] = "Immediate reorder: {$immediateStr}.";
            }
            if (!empty($monitor)) {
                $monitorStr = implode(', ', array_map(fn($x) => "{$x['name']} (plan in ~" . number_format($x['days_until_reorder'] ?? 0, 1) . "d)", $monitor));
                $summaryParts[] = "Restock soon: {$monitorStr}.";
            }
            if ($healthyCount > 0) $summaryParts[] = "{$healthyCount} product(s) healthy.";
            $detSummary = implode(' ', $summaryParts) ?: 'Reorder analysis ready.';

            return [
                'items' => $merged,
                'summary' => $detSummary,
                'data_warning' => $warn,
                'assumptions' => [
                    'lead_days' => $leadDays,
                    'safety_days' => $safetyDays,
                    'pending_pos' => 'Not tracked yet — manual check recommended',
                ],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('InventoryAiService reorderAssistant failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Reorder analysis temporarily unavailable.'];
        }
    }

    protected function parseJson(string $raw): ?array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}