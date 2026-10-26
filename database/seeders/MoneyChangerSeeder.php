<?php

namespace Database\Seeders;

use App\Models\ExchangeTransaction;
use App\Models\Provider;
use App\Models\ProviderExchangeRate;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class MoneyChangerSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::whereHas('types', fn($q) => $q->where('slug', 'money-changer'))->first();
        if (!$provider) { $this->command->warn('No money changer provider.'); return; }

        $catId = ServiceCategory::where('slug', 'money-changer')->value('id');
        $service = $provider->services()->where('service_category_id', $catId)->first();
        if (!$service) { $this->command->warn('No money changer service.'); return; }

        $currencies = [
            ['USD', 'US Dollar', '🇺🇸', 137.20, 137.90],
            ['EUR', 'Euro', '🇪🇺', 148.50, 149.35],
            ['GBP', 'British Pound', '🇬🇧', 173.40, 174.25],
            ['AUD', 'Australian Dollar', '🇦🇺', 89.60, 90.30],
            ['CAD', 'Canadian Dollar', '🇨🇦', 99.85, 100.60],
            ['SGD', 'Singapore Dollar', '🇸🇬', 101.40, 102.15],
            ['CHF', 'Swiss Franc', '🇨🇭', 154.20, 155.10],
            ['JPY', 'Japanese Yen (10)', '🇯🇵', 8.95, 9.15],
            ['CNY', 'Chinese Yuan', '🇨🇳', 18.85, 19.10],
            ['QAR', 'Qatari Riyal', '🇶🇦', 37.55, 37.95],
            ['INR', 'Indian Rupee (100)', '🇮🇳', 1.60, 1.68],
        ];

        $today = now()->toDateString();
        foreach ($currencies as [$code, $name, $flag, $buy, $sell]) {
            ProviderExchangeRate::updateOrCreate(
                ['provider_id' => $provider->id, 'currency_code' => $code, 'effective_date' => $today],
                [
                    'service_id' => $service->id,
                    'currency_name' => $name,
                    'flag_emoji' => $flag,
                    'buy_rate' => $buy,
                    'sell_rate' => $sell,
                    'is_active' => true,
                ]
            );
        }

        // Clean previous seed transactions
        $existing = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('transaction_number', 'like', 'TXN-SEED-%')
            ->delete();

        $seedTx = [
            ['Sita Gurung', 'SG', 'buy', 'USD', 1500, 137.20, 'completed'],
            ['Bikash Thapa', 'BT', 'sell', 'EUR', 800, 149.35, 'completed'],
            ['Anita Karki', 'AK', 'buy', 'GBP', 450, 173.40, 'pending'],
            ['Dipesh Rai', 'DR', 'sell', 'AUD', 1200, 90.30, 'completed'],
            ['Pooja Maharjan', 'PM', 'buy', 'CAD', 600, 99.85, 'completed'],
            ['Kiran Adhikari', 'KA', 'sell', 'USD', 2000, 137.90, 'pending'],
        ];

        $ratesMap = collect($currencies)->mapWithKeys(fn($c) => [$c[0] => ['buy' => $c[3], 'sell' => $c[4]]]);
        foreach ($seedTx as $i => [$name, $initials, $type, $cur, $amount, $rate, $status]) {
            $npr = $amount * $rate;
            $spread = $type === 'buy' ? ($ratesMap[$cur]['sell'] - $rate) : ($rate - $ratesMap[$cur]['buy']);
            $profit = abs($spread) * $amount;

            ExchangeTransaction::create([
                'transaction_number' => 'TXN-SEED-' . str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT),
                'provider_id' => $provider->id,
                'service_id' => $service->id,
                'customer_name' => $name,
                'customer_initials' => $initials,
                'type' => $type,
                'currency_code' => $cur,
                'amount_foreign' => $amount,
                'rate' => $rate,
                'npr_amount' => $npr,
                'profit' => $profit,
                'status' => $status,
            ]);
        }

        $this->command->info("Seeded {$provider->name}: 11 currencies + 6 transactions.");
    }
}