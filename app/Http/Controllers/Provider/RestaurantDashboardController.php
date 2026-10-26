<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantTable;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestaurantDashboardController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $restaurantCatId = ServiceCategory::where('slug', 'restaurant')->value('id');
        $service = $provider->services()
            ->where('service_category_id', $restaurantCatId)
            ->first();

        $currency = $service?->currency === 'USD' ? 'USD' : 'NPR';
        $currencySymbol = $currency === 'USD' ? '$' : 'Rs';

        $today = now()->startOfDay();

        $todayRevenue = (float) RestaurantOrder::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $today)
            ->sum('total');

        $todayOrders = RestaurantOrder::where('provider_id', $provider->id)
            ->where('created_at', '>=', $today)
            ->count();

        $avgOrder = (float) RestaurantOrder::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $today)
            ->avg('total');

        $tablesTotal    = RestaurantTable::where('provider_id', $provider->id)->count();
        $tablesOccupied = RestaurantTable::where('provider_id', $provider->id)->where('status', 'occupied')->count();

        // Hourly revenue (10a - 8p)
        $hourBuckets = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];
        $hourlyRaw = RestaurantOrder::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', $today)
            ->selectRaw('HOUR(paid_at) as h, SUM(total) as v')
            ->groupBy('h')
            ->pluck('v', 'h');

        $hourlyRevenue = [];
        foreach ($hourBuckets as $h) {
            $label = $h >= 12 ? (($h - 12 === 0 ? 12 : $h - 12) . 'p') : ($h . 'a');
            $hourlyRevenue[] = ['h' => $label, 'v' => (float) ($hourlyRaw[$h] ?? 0)];
        }

        // Live orders
        $liveOrdersData = RestaurantOrder::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served'])
            ->with(['items', 'table'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn($o) => [
                'db_id'  => $o->id,
                'id'     => '#' . $o->order_number,
                'table'  => $o->table?->table_number ?? 'Takeaway',
                'items'  => $o->items->map(fn($i) => "{$i->item_name} ×{$i->quantity}")->join(', ') ?: '—',
                'time'   => $o->created_at->diffForHumans(short: true),
                'total'  => (float) $o->total,
                'status' => $o->status,
            ]);

        // Tables
        $tablesData = RestaurantTable::where('provider_id', $provider->id)
            ->orderBy('table_number')
            ->get()
            ->map(fn($t) => [
                'db_id' => $t->id,
                'n'     => (int) preg_replace('/\D/', '', $t->table_number),
                'label' => $t->table_number,
                'seats' => $t->seats,
                'st'    => $t->status,
            ]);

        // Popular dishes (last 7 days)
        $dishesRaw = RestaurantOrderItem::query()
            ->join('restaurant_orders', 'restaurant_orders.id', '=', 'restaurant_order_items.restaurant_order_id')
            ->where('restaurant_orders.provider_id', $provider->id)
            ->where('restaurant_orders.created_at', '>=', now()->subDays(7))
            ->where('restaurant_orders.status', '!=', 'cancelled')
            ->groupBy('restaurant_order_items.menu_item_id', 'restaurant_order_items.item_name')
            ->selectRaw('menu_item_id, item_name, SUM(quantity) as qty, SUM(line_total) as revenue')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $maxQty = max(1, (int) $dishesRaw->max('qty'));
        $dishesData = $dishesRaw->map(function ($d) use ($provider, $maxQty, $currencySymbol) {
            $emoji = MenuItem::where('id', $d->menu_item_id)->value('emoji') ?: '🍽️';
            return [
                'emoji' => $emoji,
                'name'  => $d->item_name,
                'count' => (int) $d->qty,
                'price' => $currencySymbol . ' ' . number_format((float) $d->revenue, 0),
                'pct'   => (int) round(($d->qty / $maxQty) * 100),
            ];
        });

        // Reviews
        $reviewsData = collect();
        if ($service && class_exists(\App\Models\Review::class)) {
            try {
                $reviewsData = \App\Models\Review::where('service_id', $service->id)
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(function ($r) {
                        $name = $r->reviewer_name ?? optional($r->user)->name ?? 'Guest';
                        $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                        $colors = ['linear-gradient(135deg,#fb923c,#f97316)','linear-gradient(135deg,#3b82f6,#6366f1)','linear-gradient(135deg,#8b5cf6,#a855f7)'];
                        return [
                            'name'     => $name,
                            'initials' => $initials ?: 'G',
                            'color'    => $colors[array_rand($colors)],
                            'date'     => $r->created_at?->diffForHumans() ?? '',
                            'stars'    => (int) ($r->rating ?? 5),
                            'text'     => (string) ($r->comment ?? ''),
                        ];
                    });
            } catch (\Throwable $e) {
                \Log::warning('Restaurant reviews load failed', ['service_id' => $service->id, 'error' => $e->getMessage()]);
            }
        }

        $stats = [
            'today_revenue'   => $todayRevenue,
            'today_orders'    => $todayOrders,
            'avg_order'       => $avgOrder,
            'tables_occupied' => $tablesOccupied,
            'tables_total'    => $tablesTotal,
        ];

        return view('provider.restaurant.dashboard', [
            'provider'       => $provider,
            'service'        => $service,
            'currencySymbol' => $currencySymbol,
            'stats'          => $stats,
            'hourlyRevenue'  => $hourlyRevenue,
            'liveOrders'     => $liveOrdersData,
            'tablesData'     => $tablesData,
            'dishesData'     => $dishesData,
            'reviewsData'    => $reviewsData,
        ]);
    }

    public function updateOrderStatus(Request $request, RestaurantOrder $order)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,served,cancelled',
        ]);

        $order->status = $validated['status'];
        if ($order->status === 'served' && !$order->paid_at) {
            $order->paid_at = now();
            $order->payment_status = 'paid';
        }
        $order->save();

        return response()->json(['success' => true, 'status' => $order->status]);
    }

    public function updateTableStatus(Request $request, RestaurantTable $table)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $table->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:free,occupied,reserved,cleaning',
        ]);

        $table->status = $validated['status'];
        $table->save();

        return response()->json(['success' => true, 'status' => $table->status]);
    }
}