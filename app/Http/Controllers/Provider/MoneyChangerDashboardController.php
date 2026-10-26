<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ExchangeTransaction;
use App\Models\ProviderExchangeRate;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Auth;

class MoneyChangerDashboardController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $mcCatId = ServiceCategory::where('slug', 'money-changer')->value('id');
        $service = $provider->services()->where('service_category_id', $mcCatId)->first();

        $today = now()->startOfDay();

        $todayTxCount = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $today)
            ->count();

        $buyVolume = (float) ExchangeTransaction::where('provider_id', $provider->id)
            ->where('type', 'buy')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $today)
            ->sum('npr_amount');

        $sellVolume = (float) ExchangeTransaction::where('provider_id', $provider->id)
            ->where('type', 'sell')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $today)
            ->sum('npr_amount');

        $netProfit = (float) ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $today)
            ->sum('profit');

        // Weekly chart (last 7 days)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $weeklyRaw = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, type, SUM(npr_amount) as v')
            ->groupBy('d', 'type')
            ->get()
            ->groupBy('d');

        $weeklyData = [];
        foreach ($days as $date) {
            $dayTx = $weeklyRaw[$date] ?? collect();
            $buy  = (float) $dayTx->where('type', 'buy')->sum('v');
            $sell = (float) $dayTx->where('type', 'sell')->sum('v');
            $weeklyData[] = [
                'day'  => \Carbon\Carbon::parse($date)->format('D'),
                'buy'  => $buy,
                'sell' => $sell,
            ];
        }

        $weeklyTotal = collect($weeklyData)->sum('buy') + collect($weeklyData)->sum('sell');

        // Rates (11 currencies)
        $rates = ProviderExchangeRate::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->orderBy('currency_code')
            ->get()
            ->map(fn($r) => [
                'code'  => $r->currency_code,
                'name'  => $r->currency_name,
                'flag'  => $r->flag_emoji,
                'buy'   => (float) $r->buy_rate,
                'sell'  => (float) $r->sell_rate,
            ]);

        // Recent transactions (6)
        $recent = ExchangeTransaction::where('provider_id', $provider->id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(fn($t) => [
                'db_id'    => $t->id,
                'name'     => $t->customer_name,
                'initials' => $t->customer_initials ?? '',
                'type'     => ucfirst($t->type),
                'cur'      => $t->currency_code,
                'amt'      => number_format((float) $t->amount_foreign, 2),
                'rate'     => number_format((float) $t->rate, 2),
                'total'    => number_format((float) $t->npr_amount, 0),
                'status'   => ucfirst($t->status),
            ]);

        // Live USD rate for sidebar
        $liveUsd = $rates->firstWhere('code', 'USD');

        return view('provider.money-changer.dashboard', [
            'provider'           => $provider,
            'service'            => $service,
            'stats'              => [
                'today_tx'    => $todayTxCount,
                'buy_volume'  => $buyVolume,
                'sell_volume' => $sellVolume,
                'net_profit'  => $netProfit,
            ],
            'weeklyData'         => $weeklyData,
            'weeklyTotal'        => $weeklyTotal,
            'rates'              => $rates,
            'recentTransactions' => $recent,
            'liveUsd'            => $liveUsd,
        ]);
    }
}