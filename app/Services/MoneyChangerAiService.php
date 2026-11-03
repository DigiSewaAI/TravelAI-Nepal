<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * MoneyChangerAiService — NRB-compliant.
 * NO rate prediction, NO rate recommendation.
 * Only business intelligence on transactions.
 */
class MoneyChangerAiService
{
    public function __construct(
        protected LlmService $llm,
        protected AiLimitService $aiLimit
    ) {}

    /**
     * Daily Business Summary — today's transactions.
     */
    public function dailySummary(Provider $provider): array
    {
        $reservation = null;
        try {
            $today = now()->format('Y-m-d');
            $transactions = DB::table('exchange_transactions')
                ->where('provider_id', $provider->id)
                ->whereDate('created_at', $today)
                ->get();

            $totalNpr = (float) $transactions->sum('npr_amount');
            $totalProfit = (float) $transactions->sum('profit');
            $byCurrency = [];
            foreach ($transactions as $t) {
                $cur = (string) $t->currency_code;
                if (!isset($byCurrency[$cur])) {
                    $byCurrency[$cur] = ['count' => 0, 'volume_npr' => 0, 'profit' => 0];
                }
                $byCurrency[$cur]['count']++;
                $byCurrency[$cur]['volume_npr'] += (float) $t->npr_amount;
                $byCurrency[$cur]['profit'] += (float) $t->profit;
            }

            $detSummary = sprintf(
                'Today: %d transactions, Rs %s volume, Rs %s profit.',
                $transactions->count(),
                number_format($totalNpr, 0),
                number_format($totalProfit, 0)
            );

            if ($transactions->isEmpty()) {
                return [
                    'transactions_count' => 0,
                    'total_npr' => 0,
                    'total_profit' => 0,
                    'by_currency' => [],
                    'summary' => 'No transactions recorded today.',
                    'data_warning' => 'Add transactions to see daily summary.',
                ];
            }

            $reservation = $this->aiLimit->reserve($provider, 'moneychanger.daily_summary', []);
            if (!$reservation) {
                return [
                    'transactions_count' => $transactions->count(),
                    'total_npr' => $totalNpr,
                    'total_profit' => $totalProfit,
                    'by_currency' => $byCurrency,
                    'summary' => $detSummary . ' (AI supplement unavailable — quota reached)',
                    'fallback' => true,
                ];
            }

            $prompt = "You are a money exchange business analyst.\n"
                . "Today's transactions: {$transactions->count()}, volume Rs " . number_format($totalNpr) . ", profit Rs " . number_format($totalProfit) . "\n"
                . "By currency: " . json_encode($byCurrency) . "\n\n"
                . "CRITICAL: Do NOT suggest exchange rate changes. NRB controls rates.\n"
                . "Provide ONE summary sentence on today's business activity. "
                . "Return JSON: {\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 1000, 0.3, true);
            $data = $this->parseJson($raw);

            $detSummaryWithAi = $detSummary;
            if ($data && !empty($data['summary'])) {
                $detSummaryWithAi .= ' ' . $data['summary'];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'transactions_count' => $transactions->count(),
                'total_npr' => $totalNpr,
                'total_profit' => $totalProfit,
                'by_currency' => $byCurrency,
                'summary' => $detSummaryWithAi,
                'assumptions' => ['nrb_note' => 'NRB controls all exchange rates — no rate prediction'],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('MoneyChangerAiService dailySummary failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Daily summary unavailable.'];
        }
    }

    /**
     * Transaction Insights — last 30 days, currency + profit trends.
     */
    public function transactionInsights(Provider $provider): array
    {
        $reservation = null;
        try {
            $cutoff = now()->subDays(30);
            $transactions = DB::table('exchange_transactions')
                ->where('provider_id', $provider->id)
                ->where('created_at', '>=', $cutoff)
                ->get();

            if ($transactions->isEmpty()) {
                return ['error' => 'no_data', 'message' => 'No transactions in last 30 days.'];
            }

            $byCurrency = [];
            foreach ($transactions as $t) {
                $cur = (string) $t->currency_code;
                if (!isset($byCurrency[$cur])) {
                    $byCurrency[$cur] = ['buy_count' => 0, 'sell_count' => 0, 'buy_volume' => 0, 'sell_volume' => 0, 'profit' => 0];
                }
                if ($t->type === 'buy') {
                    $byCurrency[$cur]['buy_count']++;
                    $byCurrency[$cur]['buy_volume'] += (float) $t->amount_foreign;
                } else {
                    $byCurrency[$cur]['sell_count']++;
                    $byCurrency[$cur]['sell_volume'] += (float) $t->amount_foreign;
                }
                $byCurrency[$cur]['profit'] += (float) $t->profit;
            }

            $topProfit = null;
            $topVolume = null;
            foreach ($byCurrency as $cur => $data) {
                if ($topProfit === null || $data['profit'] > $byCurrency[$topProfit]['profit']) $topProfit = $cur;
                $totalVol = $data['buy_volume'] + $data['sell_volume'];
                if ($topVolume === null || $totalVol > ($byCurrency[$topVolume]['buy_volume'] + $byCurrency[$topVolume]['sell_volume'])) $topVolume = $cur;
            }

            $detSummary = sprintf(
                'Last 30 days: %d transactions, top-profit currency = %s, top-volume = %s.',
                $transactions->count(),
                $topProfit ?? 'N/A',
                $topVolume ?? 'N/A'
            );

            $reservation = $this->aiLimit->reserve($provider, 'moneychanger.insights', []);
            if (!$reservation) {
                return [
                    'by_currency' => $byCurrency,
                    'top_profit_currency' => $topProfit,
                    'top_volume_currency' => $topVolume,
                    'total_transactions' => $transactions->count(),
                    'summary' => $detSummary . ' (AI quota reached)',
                    'fallback' => true,
                ];
            }

            $prompt = "You are a money exchange business analyst.\n"
                . "Last 30 days by currency: " . json_encode($byCurrency) . "\n"
                . "Top profit: {$topProfit}, Top volume: {$topVolume}\n\n"
                . "CRITICAL: Do NOT recommend rate changes. NRB sets all rates. Do NOT predict future rates.\n"
                . "Provide ONE summary sentence on business insights. "
                . "Return JSON: {\"summary\":\"1 sentence\"}";

            $raw = $this->llm->generateRawTextPooled($prompt, 'Return ONLY valid JSON.', null, 1500, 0.3, true);
            $data = $this->parseJson($raw);

            $finalSummary = $detSummary;
            if ($data && !empty($data['summary'])) {
                $finalSummary .= ' ' . $data['summary'];
            }

            $this->aiLimit->finalize($reservation);
            return [
                'by_currency' => $byCurrency,
                'top_profit_currency' => $topProfit,
                'top_volume_currency' => $topVolume,
                'total_transactions' => $transactions->count(),
                'summary' => $finalSummary,
                'assumptions' => [
                    'nrb_note' => 'NRB controls all exchange rates — no rate prediction provided',
                    'period' => '30-day rolling window',
                ],
            ];
        } catch (Throwable $e) {
            if ($reservation) { try { $this->aiLimit->release($reservation); } catch (Throwable) {} }
            Log::warning('MoneyChangerAiService transactionInsights failed', ['pid' => $provider->id, 'err' => $e->getMessage()]);
            return ['error' => 'exception', 'message' => 'Insights unavailable.'];
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