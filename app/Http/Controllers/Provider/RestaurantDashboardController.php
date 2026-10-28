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

                if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'status' => $order->status]);
        }

        return redirect()->back()->with('success', 'Order status updated.');
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'status' => $table->status]);
        }

        return redirect()->back()->with('success', 'Table status updated.');
    }
        public function storeMenuItem(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:50',
            'emoji' => 'nullable|string|max:10',
        ]);

        MenuItem::create([
            'provider_id' => $provider->id,
            'service_id' => $provider->services()->first()->id,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'currency' => 'NPR',
            'category' => $validated['category'],
            'emoji' => $validated['emoji'] ?? '🍽️',
            'is_available' => true,
        ]);

        return back()->with('success', __('messages.menu_item_created_success'));
    }

    public function destroyMenuItem(MenuItem $menuItem)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $menuItem->provider_id === $provider->id, 403);
        
        $menuItem->delete();
        return back()->with('success', __('messages.menu_item_deleted_success'));
    }

    public function storeOrder(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $total = 0;
        $orderItemsData = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::find($item['id']);
            $lineTotal = $menuItem->price * $item['qty'];
            $total += $lineTotal;

            $orderItemsData[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'unit_price' => $menuItem->price,
                'quantity' => $item['qty'],
                'line_total' => $lineTotal,
            ];
        }

        $order = RestaurantOrder::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'provider_id' => $provider->id,
            'service_id' => $provider->services()->first()->id,
            'table_id' => $validated['table_id'],
            'status' => 'pending',
            'subtotal' => $total,
            'tax' => 0,
            'total' => $total,
            'currency' => 'NPR',
            'payment_status' => 'unpaid',
        ]);

        $order->items()->createMany($orderItemsData);

        // Mark table as occupied
        RestaurantTable::where('id', $validated['table_id'])->update(['status' => 'occupied']);

        return back()->with('success', __('messages.order_created_success'));
    }

    public function destroyOrder(RestaurantOrder $order)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        // Free the table
        if ($order->table) {
            $order->table->update(['status' => 'available']);
        }

        $order->delete();
        return back()->with('success', __('messages.order_deleted_success'));
    }
        public function ordersPage()
    {
        $provider = Auth::user()->ownProvider();
        $orders = RestaurantOrder::where('provider_id', $provider->id)->with('items')->latest()->get();
        return view('provider.restaurant.orders', compact('provider', 'orders'));
    }

    public function menuPage()
    {
        $provider = Auth::user()->ownProvider();
        $menuItems = MenuItem::where('provider_id', $provider->id)->orderBy('category')->get();
        return view('provider.restaurant.menu', compact('provider', 'menuItems'));
    }

    public function tablesPage()
    {
        $provider = Auth::user()->ownProvider();
        $tables = RestaurantTable::where('provider_id', $provider->id)->orderBy('table_number')->get();
        return view('provider.restaurant.tables', compact('provider', 'tables'));
    }
        public function reportsPage()
    {
        $provider = Auth::user()->ownProvider();
        $totalSales = RestaurantOrder::where('provider_id', $provider->id)->where('status', '!=', 'cancelled')->sum('total');
        $totalOrders = RestaurantOrder::where('provider_id', $provider->id)->count();
        $avgOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $orders = RestaurantOrder::where('provider_id', $provider->id)->latest()->take(10)->get();
        
        return view('provider.restaurant.reports', compact('provider', 'totalSales', 'totalOrders', 'avgOrder', 'orders'));
    }

    public function offersPage()
    {
        $provider = Auth::user()->ownProvider();
        // Dummy offers for UI (No offers table yet in R8 scope)
        $offers = collect([
            (object)['title' => 'Happy Hour (4-6 PM)', 'discount' => '20% Off', 'valid_until' => now()->addDays(7), 'status' => 'active'],
            (object)['title' => 'Weekend Family Combo', 'discount' => '15% Off', 'valid_until' => now()->addDays(14), 'status' => 'active'],
            (object)['title' => 'Student Discount', 'discount' => '10% Off', 'valid_until' => now()->addDays(30), 'status' => 'inactive'],
        ]);
        
        return view('provider.restaurant.offers', compact('provider', 'offers'));
    }
        public function updateMenuItem(Request $request, MenuItem $menuItem)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $menuItem->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'emoji' => 'nullable|string|max:10',
        ]);

        $menuItem->update($validated);
        return redirect()->back()->with('success', 'Menu item updated.');
    }

    public function storeTable(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'table_number' => 'required|string|max:20',
            'seats' => 'required|integer|min:1',
            'status' => 'nullable|in:free,occupied,reserved,cleaning',
        ]);

        $exists = RestaurantTable::where('provider_id', $provider->id)
            ->where('table_number', $validated['table_number'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['table_number' => 'This table number already exists.'])->withInput();
        }

        $serviceId = RestaurantTable::where('provider_id', $provider->id)
            ->whereNotNull('service_id')
            ->value('service_id');

        RestaurantTable::create([
            'provider_id' => $provider->id,
            'service_id' => $serviceId,
            'table_number' => $validated['table_number'],
            'seats' => $validated['seats'],
            'status' => $validated['status'] ?? 'free',
        ]);

        return redirect()->back()->with('success', 'Table added successfully.');
    }

    public function updateTable(Request $request, RestaurantTable $table)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $table->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'table_number' => 'required|string|max:20',
            'seats' => 'required|integer|min:1',
            'status' => 'required|in:free,occupied,reserved,cleaning',
        ]);

        $exists = RestaurantTable::where('provider_id', $provider->id)
            ->where('table_number', $validated['table_number'])
            ->where('id', '!=', $table->id)
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['table_number' => 'This table number already exists.'])->withInput();
        }

        $table->update($validated);

        return redirect()->back()->with('success', 'Table updated successfully.');
    }

    public function destroyTable(RestaurantTable $table)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $table->provider_id === $provider->id, 403);

        if ($table->currentOrder()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete this table - it has active orders.');
        }

        $table->delete();

        return redirect()->back()->with('success', 'Table deleted successfully.');
    }
}