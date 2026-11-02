<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * RestaurantAiService — Menu Intelligence + Demand Forecast.
 * Deterministic-first, AI-refined. Handles zero-order_items gracefully.
 */
class RestaurantAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    /**
     * Menu Intelligence — popular / low / dead items analysis.
     * If restaurant_order_items = 0, returns deterministic "no history" message.
     */
    public function menuIntelligence(Provider $provider): array
    {
        $reservation = null;
        try {
            $items = DB::table('menu_items')->where('provider_id', $provider->id)->get();
            if ($items->isEmpty()) {
                return ['error' => 'no_menu', 'message' => 'Add menu items first.'];
            }

            $itemStats = [];
            foreach ($items as $item) {
                $agg = DB::table('restaurant_order_items')
                    ->where('menu_item_id', $item->id)
                    ->select(DB::raw('COUNT(*) total_orders'), DB::raw('SUM(quantity) total_qty'), DB::raw('SUM(line_total) revenue'))
                    ->first();
                $itemStats[] = [
                    'name' => (string) $item->name,
                    'category' => (string) $item->category,
                    'price' => (float) $item->price,
                    'times_ordered' => (int) ($agg->total_orders ?? 0),
                    'total_quantity' => (int) ($agg->total_qty ?? 0),
                    'revenue' => (float) ($agg->revenue ?? 0),
                ];
            }

            $totalOrders = array_sum(array_column($itemStats, 'times_ordered'));
            $warn = $totalOrders < 5
                ? "Very low order data ({$totalOrders} item orders) — insights are baseline only."
                : null;

            // Deterministic classification
            $classified = $this->classifyMenuItems($itemStats);

            // If no orders at all → skip AI, return deterministic
            if ($totalOrders === 0) {
                return [
                    'items' => $classified,
                    'summary' => 'No order history yet. Recommendations based on price tiers.',
                    'data_warning' => 'Order items not yet tracked — add orders to enable intelligence.',
                    'fallback' => true,
                ];
            }

            $reservation = $this->aiLimit->reserve($provider, 'restaurant.menu_intelligence', []);
            if (!$reservation) {
                return ['items' => $classified, 'summary' => 'Deterministic baseline (AI quota reached).', 'data_warning' => $warn];
            }

            $prompt = "You are a restaurant menu analyst.\n"
                . "Menu items with 30-day order stats:\n" . json_encode($itemStats) . "\n\n"
                . "For each item, return:\n"
                . "  - name\n"
                . "  - status (popular|steady|slow|dead)\n"
                . "  - suggestion (1 short sentence — choose from: 'Keep', 'Promote', 'Improve visibility', 'Keep monitoring'. NEVER use 'Review pricing', 'Rebrand', or 'Remove' unless strong evidence exists — 3 orders or less is INSUFFICIENT evidence for price conclusions)\n"
                . "  - reason (quote actual orders/revenue)\n"
                . "Do NOT invent data. If times_ordered=0, classify as 'dead'.\n"
                . "Return JSON: {\"items\":[...],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2000, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data || empty($data['items'])) {
                $this->aiLimit->release($reservation);
                return ['items' => $classified, 'summary' => 'Deterministic baseline (AI parse failed).', 'data_warning' => $warn];
            }

            $this->aiLimit->finalize($reservation);
            return ['items' => $data['items'], 'summary' => (string)($data['summary'] ?? 'Menu ready.'), 'data_warning' => $warn];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('RestaurantAiService menuIntelligence failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Menu analysis temporarily unavailable.'];
        }
    }

    /**
     * Demand Forecast — next 7 days expected orders + revenue.
     * Uses restaurant_orders (date + total).
     */
    public function demandForecast(Provider $provider, int $days = 7): array
    {
        $reservation = null;
        try {
            $last30 = DB::table('restaurant_orders')
                ->where('provider_id', $provider->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->where('status', '!=', 'cancelled')
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c, SUM(total) as rev')
                ->groupBy('d')->orderBy('d')->get()
                ->map(fn($r) => ['date' => $r->d, 'orders' => (int)$r->c, 'revenue' => (float)$r->rev])
                ->toArray();

            if (empty($last30)) {
                return ['error' => 'no_history', 'message' => 'Not enough order history to forecast.'];
            }

            $totalOrders = array_sum(array_column($last30, 'orders'));
            $totalRevenue = array_sum(array_column($last30, 'revenue'));
            $avgOrders = round($totalOrders / 30, 2);
            $avgRev = round($totalRevenue / 30, 2);
            // AOV = TOTAL revenue / TOTAL orders (no double-rounding)
            $trueAvgOrderValue = $totalOrders > 0 ? ($totalRevenue / $totalOrders) : 0;

            $reservation = $this->aiLimit->reserve($provider, 'restaurant.demand_forecast', ['days' => $days]);
            if (!$reservation) {
                return ['error' => 'quota_exceeded', 'message' => 'AI quota reached.'];
            }

            $prompt = "Restaurant order demand forecast. Today: " . now()->format('Y-m-d') . ".\n"
                . "Last 30 days daily orders: " . json_encode($last30) . "\n"
                . "Avg orders/day: {$avgOrders}, avg revenue/day: Rs {$avgRev}\n\n"
                . "Forecast next {$days} days starting tomorrow (" . now()->addDay()->format('Y-m-d') . "). "
                . "Per day: date, predicted_orders(int), predicted_revenue(int NPR), confidence(low|med|high). "
                . "If data is very sparse (<5 days), use low confidence.\n"
                . "Return JSON: {\"days\":[...],\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 2000, 0.3, true);
            $data = $this->parseJson($raw);

            if (!$data) {
                $this->aiLimit->release($reservation);
                return ['error' => 'parse_failed', 'message' => 'AI format issue.'];
            }

            $this->aiLimit->finalize($reservation);

            // AOV = totalRevenue / totalOrders (verified, no double-round)
            $avgOrderValue = $trueAvgOrderValue;
            $days = $data['days'] ?? [];
            $minOrders = max(0, (int) floor($avgOrders * 0.5));
            $maxOrders = max(2, (int) ceil($avgOrders * 1.5));
            $totalOrdersForecast = 0; $totalRevenueForecast = 0;

            foreach ($days as $idx => $d) {
                $po = (int) ($d['predicted_orders'] ?? 0);
                $po = max($minOrders, min($maxOrders, $po ?: (int) round($avgOrders)));
                $days[$idx]['predicted_orders'] = $po;
                // revenue STRICTLY = orders × avgOrderValue (never historical avg)
                $days[$idx]['predicted_revenue'] = (int) round($po * $avgOrderValue);
                $totalOrdersForecast += $po;
                $totalRevenueForecast += $days[$idx]['predicted_revenue'];
            }

            $summary = (string) ($data['summary'] ?? '');
            if ($avgOrders < 1) {
                $summary = sprintf(
                    'Baseline estimate only: avg %.2f orders/day historical. Forecast shows %d-%d orders/day × Rs %d/order = %d-%d revenue/day. Low confidence — insufficient history.',
                    $avgOrders, $minOrders, $maxOrders,
                    (int) round($avgOrderValue),
                    (int) round($minOrders * round($avgOrderValue)),
                    (int) round($maxOrders * round($avgOrderValue))
                );
            }

            return [
                'days' => $days,
                'summary' => $summary,
                'avg_orders_per_day' => $avgOrders,
                'avg_revenue_per_day' => (int) round($avgRev),
                'avg_order_value' => (int) round($avgOrderValue),
                'forecast_total_orders' => $totalOrdersForecast,
                'forecast_total_revenue' => $totalRevenueForecast,
                'range_min_revenue' => (int) round($minOrders * round($avgOrderValue)),
                'range_max_revenue' => (int) round($maxOrders * round($avgOrderValue)),
                'data_quality' => $avgOrders < 1 ? 'insufficient' : ($avgOrders < 3 ? 'sparse' : 'ok'),
                'is_baseline' => true, // all daily values are baseline estimates, not date-specific
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('RestaurantAiService demandForecast failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Forecast temporarily unavailable.'];
        }
    }

    protected function classifyMenuItems(array $items): array
    {
        $out = [];
        foreach ($items as $i) {
            // If order_items tracking exists but this item has 0 → "slow"
            // If NO order_items tracked at all (system-level) → "insufficient"
            $status = 'insufficient_data';
            $suggestion = 'Item-level sales tracking unavailable';
            $reason = 'Item-level order tracking not yet enabled — cannot classify';

            if ($i['times_ordered'] >= 5) {
                $status = 'popular'; $suggestion = 'Keep + feature prominently';
                $reason = "Ordered {$i['times_ordered']}x, Rs " . number_format($i['revenue']) . ' revenue';
            } elseif ($i['times_ordered'] >= 2) {
                $status = 'steady'; $suggestion = 'Keep';
                $reason = "Ordered {$i['times_ordered']}x, Rs " . number_format($i['revenue']) . ' revenue';
            } elseif ($i['times_ordered'] >= 1) {
                $status = 'slow'; $suggestion = 'Promote';
                $reason = "Ordered {$i['times_ordered']}x, Rs " . number_format($i['revenue']) . ' revenue';
            }
            // times_ordered = 0 → stays "insufficient_data" (not "dead")

            $out[] = ['name' => $i['name'], 'status' => $status, 'suggestion' => $suggestion, 'reason' => $reason];
        }
        return $out;
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