<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ExchangeTransaction;
use App\Models\ProviderExchangeRate;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
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
                'db_id' => $r->id,           // 🆕 थपियो — delete button काम गर्न
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

    public function storeRate(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'currency_code' => 'required|string|max:10',
            'currency_name' => 'nullable|string|max:100',
            'buy_rate' => 'required|numeric|min:0',
            'sell_rate' => 'required|numeric|min:0',
            'flag_emoji' => 'nullable|string|max:10',
        ]);

        ProviderExchangeRate::updateOrCreate(
            [
                'provider_id' => $provider->id,
                'currency_code' => strtoupper($validated['currency_code']),
                'effective_date' => now()->toDateString()
            ],
            [
                'service_id' => $provider->services()->first()->id,
                'currency_name' => $validated['currency_name'] ?? strtoupper($validated['currency_code']),
                'flag_emoji' => $validated['flag_emoji'] ?? '💱',
                'buy_rate' => $validated['buy_rate'],
                'sell_rate' => $validated['sell_rate'],
                'is_active' => true,
            ]
        );

        return back()->with('success', __('messages.rate_created_success'));
    }

    public function destroyRate(ProviderExchangeRate $rate)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $rate->provider_id === $provider->id, 403);

        $rate->delete();
        return back()->with('success', __('messages.rate_deleted_success'));
    }

    public function storeTransaction(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'type' => 'required|in:buy,sell',
            'currency_code' => 'required|string|max:10',
            'amount_foreign' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0.0001',
        ]);

        $nprAmount = $validated['amount_foreign'] * $validated['rate'];

        // Simple profit calculation (Assuming 2% margin if exact spread isn't known)
        $profit = $nprAmount * 0.02;

        ExchangeTransaction::create([
            'transaction_number' => 'TXN-' . strtoupper(uniqid()),
            'provider_id' => $provider->id,
            'service_id' => $provider->services()->first()->id,
            'customer_name' => $validated['customer_name'],
            'customer_initials' => collect(explode(' ', $validated['customer_name']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join(''),
            'type' => $validated['type'],
            'currency_code' => strtoupper($validated['currency_code']),
            'amount_foreign' => $validated['amount_foreign'],
            'rate' => $validated['rate'],
            'npr_amount' => $nprAmount,
            'profit' => $profit,
            'status' => 'completed',
        ]);

        return back()->with('success', __('messages.transaction_created_success'));
    }

    public function destroyTransaction(ExchangeTransaction $transaction)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $transaction->provider_id === $provider->id, 403);

        $transaction->delete();
        return back()->with('success', __('messages.transaction_deleted_success'));
    }
        public function ratesPage()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $rates = ProviderExchangeRate::where('provider_id', $provider->id)
            ->orderBy('currency_code')
            ->orderByDesc('effective_date')
            ->get();

        $activeCount = $rates->where('is_active', true)->count();
        $currencyCount = $rates->pluck('currency_code')->unique()->count();

        return view('provider.money-changer.rates', compact('provider', 'rates', 'activeCount', 'currencyCount'));
    }

    public function transactionsPage()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $transactions = ExchangeTransaction::where('provider_id', $provider->id)
            ->orderByDesc('created_at')
            ->get();

        $totalVolume = (float) $transactions->where('status', '!=', 'cancelled')->sum('npr_amount');
        $totalProfit = (float) $transactions->where('status', '!=', 'cancelled')->sum('profit');
        $buyCount = $transactions->where('type', 'buy')->where('status', '!=', 'cancelled')->count();
        $sellCount = $transactions->where('type', 'sell')->where('status', '!=', 'cancelled')->count();

        return view('provider.money-changer.transactions', compact('provider', 'transactions', 'totalVolume', 'totalProfit', 'buyCount', 'sellCount'));
    }

    public function customersPage()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $customers = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('customer_name, customer_initials, COUNT(*) as tx_count, SUM(npr_amount) as total_volume, SUM(profit) as total_profit, MAX(created_at) as last_tx_at')
            ->groupBy('customer_name', 'customer_initials')
            ->orderByDesc('total_volume')
            ->get();

        $totalCustomers = $customers->count();
        $repeatCustomers = $customers->where('tx_count', '>', 1)->count();
        $totalVolume = (float) $customers->sum('total_volume');

        return view('provider.money-changer.customers', compact('provider', 'customers', 'totalCustomers', 'repeatCustomers', 'totalVolume'));
    }

    public function reportsPage()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $today = now()->startOfDay();

        $allTimeVolume = (float) ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->sum('npr_amount');
        $allTimeProfit = (float) ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->sum('profit');
        $totalTxCount = ExchangeTransaction::where('provider_id', $provider->id)->count();
        $todayVolume = (float) ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $today)
            ->sum('npr_amount');

        // 7-day buy vs sell chart (same pattern as dashboard index)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $weeklyRaw = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, type, SUM(npr_amount) as v')
            ->groupBy('d', 'type')
            ->get()
            ->groupBy('d');

        $chartData = [];
        foreach ($days as $date) {
            $dayTx = $weeklyRaw[$date] ?? collect();
            $chartData[] = [
                'day'  => \Carbon\Carbon::parse($date)->format('D'),
                'buy'  => (float) $dayTx->where('type', 'buy')->sum('v'),
                'sell' => (float) $dayTx->where('type', 'sell')->sum('v'),
            ];
        }
        $chartTotal = collect($chartData)->sum('buy') + collect($chartData)->sum('sell');

        // Top 5 currencies by volume
        $topCurrencies = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('currency_code, COUNT(*) as tx_count, SUM(npr_amount) as total_volume')
            ->groupBy('currency_code')
            ->orderByDesc('total_volume')
            ->limit(5)
            ->get();

        // Top 5 customers by volume
        $topCustomers = ExchangeTransaction::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('customer_name, SUM(npr_amount) as total_volume, COUNT(*) as tx_count')
            ->groupBy('customer_name')
            ->orderByDesc('total_volume')
            ->limit(5)
            ->get();

        return view('provider.money-changer.reports', compact(
            'provider', 'allTimeVolume', 'allTimeProfit', 'totalTxCount', 'todayVolume',
            'chartData', 'chartTotal', 'topCurrencies', 'topCustomers'
        ));
    }

    public function updateRate(Request $request, ProviderExchangeRate $rate)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $rate->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'currency_code' => 'required|string|max:10',
            'currency_name' => 'nullable|string|max:100',
            'buy_rate'      => 'required|numeric|min:0',
            'sell_rate'     => 'required|numeric|min:0',
            'flag_emoji'    => 'nullable|string|max:16',
        ]);

        $rate->update($validated);

        return redirect()->back()->with('success', __('messages.rate_updated'));
    }
}
