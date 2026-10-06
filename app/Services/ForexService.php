<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForexService
{
    const API_URL   = 'https://open.er-api.com/v6/latest/USD';
    const TARGET    = 'NPR';
    const CACHE_TTL = 21600;   // 6 hours

    // Currencies to derive (cross-rate via USD)
    const CURRENCIES = ['USD', 'EUR', 'GBP', 'INR', 'AUD'];

    /**
     * Fetch all currencies in ONE call.
     * Returns: ['USD' => 154.19, 'EUR' => 165.42, ...] (NPR per 1 unit)
     */
    public function getAllRates(): array
    {
        return Cache::remember('forex:all_rates_v1', self::CACHE_TTL, function () {
            $rates = $this->fetchFromApi();

            if (!empty($rates)) {
                $this->store($rates);
                return $rates;
            }

            // Fallback: DB (last stored)
            return ExchangeRate::where('target_currency', self::TARGET)
                ->latest('fetched_at')
                ->get()
                ->keyBy('base_currency')
                ->map(fn ($r) => (float) $r->rate)
                ->toArray();
        });
    }

    /**
     * Single rate lookup.
     */
    public function getRate(string $base): ?float
    {
        $base  = strtoupper($base);
        $rates = $this->getAllRates();
        return $rates[$base] ?? null;
    }

    /**
     * API call → build cross-rates → NPR-per-unit.
     */
    private function fetchFromApi(): array
    {
        try {
            $response = Http::timeout(5)->get(self::API_URL);

            if (!$response->successful()) {
                Log::warning('Forex API non-success', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            if (($data['result'] ?? null) !== 'success') {
                Log::warning('Forex API error result', ['data' => $data]);
                return [];
            }

            $usdRates  = $data['rates'] ?? [];
            $nprPerUsd = $usdRates[self::TARGET] ?? null;

            if (!$nprPerUsd) {
                return [];
            }

            $out = [];
            foreach (self::CURRENCIES as $cur) {
                if ($cur === 'USD') {
                    $out['USD'] = round((float) $nprPerUsd, 6);
                } else {
                    $curPerUsd = $usdRates[$cur] ?? null;
                    if ($curPerUsd && $curPerUsd > 0) {
                        $out[$cur] = round((float) $nprPerUsd / (float) $curPerUsd, 6);
                    }
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::warning('Forex API exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Persist rates.
     */
    private function store(array $rates): void
    {
        $now = now();
        foreach ($rates as $base => $rate) {
            ExchangeRate::updateOrCreate(
                ['base_currency' => $base, 'target_currency' => self::TARGET],
                ['rate' => $rate, 'fetched_at' => $now]
            );
        }
    }
}