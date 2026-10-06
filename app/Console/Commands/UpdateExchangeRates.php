<?php

namespace App\Console\Commands;

use App\Services\ForexService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateExchangeRates extends Command
{
    protected $signature = 'forex:update';
    protected $description = 'Fetch latest exchange rates from API and refresh cache';

    public function handle(): int
    {
        $this->info('Updating exchange rates...');

        // Clear cache so next call triggers fresh API fetch
        Cache::forget('forex:all_rates_v1');

        $forex = app(ForexService::class);
        $rates = $forex->getAllRates();

        if (empty($rates)) {
            $this->warn('No rates fetched — API may be down. DB fallback active.');
            return 1;
        }

        $this->line('  Rates updated:');
        foreach ($rates as $currency => $rate) {
            $this->line("   1 {$currency} → NPR " . number_format($rate, 2));
        }

        $this->info('✅ Forex rates updated.');
        return 0;
    }
}