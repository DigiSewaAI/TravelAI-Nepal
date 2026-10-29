<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $today = now()->startOfDay();

        // KPI: Total active products
        $totalProducts = Product::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->count();

        // KPI: Low stock count
        $lowStockCount = Product::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        // KPI: Orders today
        $ordersToday = Order::whereHas('items', fn($q) => $q->where('provider_id', $provider->id))
            ->where('created_at', '>=', $today)
            ->count();

        // KPI: Stock value (stock * cost_price fallback price)
        $stockValue = (float) Product::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->selectRaw('SUM(stock_quantity * COALESCE(cost_price, price)) as total')
            ->value('total') ?? 0;

        // Stock health %
        $inStockCount = Product::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        // Weekly chart — sales vs rentals (last 7 days)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));

        $chartRaw = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('order_items.provider_id', $provider->id)
            ->where('orders.created_at', '>=', now()->subDays(6)->startOfDay())
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('DATE(orders.created_at) as d, products.product_type, SUM(order_items.line_total) as v')
            ->groupBy('d', 'product_type')
            ->get()
            ->groupBy('d');

        $chartData = [];
        foreach ($days as $date) {
            $dayTx = $chartRaw[$date] ?? collect();
            $sales   = (float) $dayTx->where('product_type', 'shop')->sum('v')
                     + (float) $dayTx->where('product_type', 'wholesale')->sum('v');
            $rentals = (float) $dayTx->where('product_type', 'rental')->sum('v');
            $chartData[] = [
                'day'     => \Carbon\Carbon::parse($date)->format('D'),
                'sales'   => $sales,
                'rentals' => $rentals,
            ];
        }
        $chartTotal = collect($chartData)->sum('sales') + collect($chartData)->sum('rentals');

        // Low stock alerts
        $lowStockAlerts = Product::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderByRaw('(stock_quantity / GREATEST(low_stock_threshold, 1)) ASC')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'db_id' => $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'stock' => (int) $p->stock_quantity,
                'min'   => (int) $p->low_stock_threshold,
                'emoji' => $this->emojiFor($p),
            ]);

        // Quick stock update products
        $allProducts = Product::where('provider_id', $provider->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'db_id' => $p->id,
                'name'  => $p->name,
                'sku'   => $p->sku,
                'stock' => (int) $p->stock_quantity,
                'min'   => (int) $p->low_stock_threshold,
                'price' => (float) $p->price,
                'type'  => $p->product_type,
                'emoji' => $this->emojiFor($p),
            ]);

        // Recent orders
        $recentOrders = Order::whereHas('items', fn($q) => $q->where('provider_id', $provider->id))
            ->with(['items' => fn($q) => $q->where('provider_id', $provider->id), 'user'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($o) {
                $firstItem = $o->items->first();
                $modeRaw = $firstItem?->product_type ?? 'shop';
                $mode = match($modeRaw) {
                    'rental'    => 'Rental',
                    'wholesale' => 'Wholesale',
                    default     => 'Sale',
                };
                $name = $o->user?->name ?? 'Guest';
                $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') ?: 'G';
                return [
                    'id'       => $o->order_number ?? ('#ORD-' . $o->id),
                    'name'     => $name,
                    'initials' => $initials,
                    'items'    => $o->items->map(fn($i) => $i->product_name . ' ×' . $i->quantity)->join(', '),
                    'mode'     => $mode,
                    'amount'   => (float) $o->total,
                    'status'   => $o->status,
                ];
            });

        // PHASE-2: top products by sales (dashboard)
        $topProducts = \App\Models\OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.provider_id', $provider->id)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_name, SUM(order_items.line_total) as total_sales, SUM(order_items.quantity) as total_units')
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        return view('provider.inventory.dashboard', [
            'provider'     => $provider,
            'stats'        => [
                'total_products'    => $totalProducts,
                'low_stock_count'   => $lowStockCount,
                'orders_today'      => $ordersToday,
                'stock_value'       => $stockValue,
                'stock_health_pct'  => $stockHealth,
            ],
            'chartData'    => $chartData,
            'chartTotal'   => $chartTotal,
            'lowStockAlerts' => $lowStockAlerts,
            'allProducts'  => $allProducts,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
        ]);
    }

    public function updateStock(Request $request, Product $product)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $product->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'quantity' => 'required|integer|not_in:0',
        ]);

        $newStock = DB::transaction(function () use ($product, $validated) {
            $locked = Product::where('id', $product->id)->lockForUpdate()->first();
            $previous = (int) $locked->stock_quantity;
            $new = max(0, $previous + (int) $validated['quantity']);

            $locked->stock_quantity = $new;
            $locked->save();

            InventoryMovement::create([
                'provider_id'    => $locked->provider_id,
                'product_id'     => $locked->id,
                'type'           => $validated['quantity'] > 0 ? 'in' : 'out',
                'quantity'       => (int) $validated['quantity'],
                'previous_stock' => $previous,
                'new_stock'      => $new,
                'reason'         => $validated['quantity'] > 0 ? 'manual_add' : 'manual_remove',
                'created_by'     => Auth::id(),
            ]);

            return $new;
        });

        return response()->json([
            'success' => true,
            'new_stock' => $newStock,
        ]);
    }

    private function emojiFor(Product $p): string
    {
        $type = $p->product_type;
        return match($type) {
            'rental'    => '🚲',
            'wholesale' => '📦',
            default     => '🛍️',
        };
    }
        public function storeProduct(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'product_type' => 'required|in:shop,rental,wholesale',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'emoji' => 'nullable|string|max:10',
        ]);

        // Generate SKU
        $sku = 'SKU-' . $provider->id . '-' . strtoupper(Str::random(4));

        Product::create([
            'provider_id' => $provider->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'price' => $validated['price'],
            'product_type' => $validated['product_type'],
            'stock_quantity' => $validated['stock_quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'],
            'sku' => $sku,
            'emoji' => $validated['emoji'] ?? '📦',
            'status' => 'active',
        ]);

        return back()->with('success', __('messages.product_created_success'));
    }

    public function destroyProduct(Product $product)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $product->provider_id === $provider->id, 403);

        $product->delete();
        return back()->with('success', __('messages.product_deleted_success'));
    }
        public function productsPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');
        $products = Product::where('provider_id', $provider->id)->orderBy('name')->get();
        
        // Sidebar Health Calculation
        $totalProducts = $products->count();
        $inStockCount = $products->where('stock_quantity', '>', 0)->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        return view('provider.inventory.products', compact('provider', 'products', 'stockHealth'));
    }

    public function ordersPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');
        $orderIds = OrderItem::where('provider_id', $provider->id)->pluck('order_id')->unique();
        $orders = Order::whereIn('id', $orderIds)->with('user')->latest()->get();

        // Sidebar Health Calculation
        $totalProducts = Product::where('provider_id', $provider->id)->count();
        $inStockCount = Product::where('provider_id', $provider->id)->where('stock_quantity', '>', 0)->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        return view('provider.inventory.orders', compact('provider', 'orders', 'stockHealth'));
    }
        public function inventoryPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $products = Product::where('provider_id', $provider->id)->orderBy('name')->get();

        $totalProducts = $products->count();
        $inStockCount = $products->where('stock_quantity', '>', 0)->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        $healthy = $products->filter(fn($p) => $p->stock_quantity > $p->low_stock_threshold);
        $low = $products->filter(fn($p) => $p->stock_quantity > 0 && $p->stock_quantity <= $p->low_stock_threshold);
        $out = $products->filter(fn($p) => $p->stock_quantity <= 0);

        return view('provider.inventory.inventory', compact('provider', 'products', 'stockHealth', 'healthy', 'low', 'out', 'totalProducts'));
    }

    public function suppliersPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $totalProducts = Product::where('provider_id', $provider->id)->count();
        $inStockCount = Product::where('provider_id', $provider->id)->where('stock_quantity', '>', 0)->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        $suppliers = collect([]);

        return view('provider.inventory.suppliers', compact('provider', 'suppliers', 'stockHealth'));
    }

    public function reportsPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $totalProducts = Product::where('provider_id', $provider->id)->where('status', 'active')->count();
        $lowStockCount = Product::where('provider_id', $provider->id)->where('status', 'active')
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $stockValue = (float) Product::where('provider_id', $provider->id)->where('status', 'active')
            ->selectRaw('SUM(stock_quantity * COALESCE(cost_price, price)) as total')->value('total') ?? 0;

        $ordersToday = Order::whereHas('items', fn($q) => $q->where('provider_id', $provider->id))
            ->where('created_at', '>=', now()->startOfDay())->count();

        $inStockCount = Product::where('provider_id', $provider->id)->where('status', 'active')
            ->where('stock_quantity', '>', 0)->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        $topProducts = Product::where('provider_id', $provider->id)->where('status', 'active')
            ->selectRaw('*, (stock_quantity * COALESCE(cost_price, price)) as total_value')
            ->orderByDesc('total_value')->limit(5)->get();

        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $chartRaw = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.provider_id', $provider->id)
            ->where('orders.created_at', '>=', now()->subDays(6)->startOfDay())
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('DATE(orders.created_at) as d, SUM(order_items.line_total) as v')
            ->groupBy('d')->get()->keyBy('d');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'value' => (float) (($chartRaw[$date]->v ?? 0)),
            ];
        }
        $chartTotal = collect($chartData)->sum('value');

        return view('provider.inventory.reports', compact('provider', 'totalProducts', 'lowStockCount', 'stockValue', 'ordersToday', 'stockHealth', 'topProducts', 'chartData', 'chartTotal'));
    }

    public function offersPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $totalProducts = Product::where('provider_id', $provider->id)->count();
        $inStockCount = Product::where('provider_id', $provider->id)->where('stock_quantity', '>', 0)->count();
        $stockHealth = $totalProducts > 0 ? (int) round(($inStockCount / $totalProducts) * 100) : 100;

        $offers = collect([
            (object)['title' => 'Seasonal Discount', 'discount' => '15% Off', 'valid_until' => now()->addDays(14), 'status' => 'active'],
            (object)['title' => 'Bulk Purchase Deal', 'discount' => '20% Off', 'valid_until' => now()->addDays(30), 'status' => 'active'],
            (object)['title' => 'Rental Weekend Special', 'discount' => '10% Off', 'valid_until' => now()->addDays(7), 'status' => 'inactive'],
        ]);

        return view('provider.inventory.offers', compact('provider', 'offers', 'stockHealth'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $product->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'product_type' => 'required|in:shop,rental,wholesale',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully.');
    }
}