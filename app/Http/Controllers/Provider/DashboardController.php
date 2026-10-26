<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QrScan;
use App\Models\Service;
use App\Services\AiLimitService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(AiLimitService $aiLimit)
    {
        // 🆕 Type-based switch — vertical dashboards (Restaurant)
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->getCurrentProvider()?->types->contains('slug', 'restaurant')) {
            return redirect()->route('provider.restaurant.dashboard');
        }
        
        // 🆕 Money Changer vertical dashboard
        if ($user && $user->getCurrentProvider()?->types->contains('slug', 'money-changer')) {
            return redirect()->route('provider.money-changer.dashboard');
        }
        
        // 🆕 Inventory (product providers) vertical dashboard
        if ($user && $user->getCurrentProvider()?->types->whereIn('slug', ['shop-owner', 'rental-provider', 'wholesale-provider'])->isNotEmpty()) {
            return redirect()->route('provider.inventory.dashboard');
        }
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $provider = $user->getCurrentProvider();

        if (!$provider) {
            return redirect()->route('home')->with('error', 'Provider profile not found.');
        }

        // Check if this is a Product Provider (Shop, Rental, Wholesale)
        $productSlugs = ['shop-owner', 'rental-provider', 'wholesale-provider'];
        $providerTypeSlug = $provider->types->first()?->slug;
        $isProductProvider = in_array($providerTypeSlug, $productSlugs);

        // 1. Services Data (for Service Providers)
        $services = $provider->services()->get();
        $serviceIds = $services->pluck('id');
        $totalServices = $services->count();

        $bookingsQuery = Booking::whereIn('service_id', $serviceIds);
        $totalBookings = $bookingsQuery->count();
        $pendingBookings = $bookingsQuery->where('status', 'pending')->count();

        $recentBookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['traveler', 'service'])->latest()->take(10)->get();

        $bookingsTrend = Booking::whereIn('service_id', $serviceIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        $topServices = Service::whereIn('id', $serviceIds)
            ->withCount('bookings')->orderBy('bookings_count', 'desc')->limit(5)->get();

        // 2. Products & Orders Data (for Product Providers)
        $products = $provider->products()->get();
        $totalProducts = $products->count();
        
        $ordersTrend = collect();
        $recentOrders = collect();
        $topProducts = collect(); // ✅ नयाँ थपियो

        if ($isProductProvider) {
            // Get all order IDs that contain this provider's products
            $orderIds = OrderItem::where('provider_id', $provider->id)->pluck('order_id')->unique();
            
            // Orders Trend (Last 30 Days)
            $ordersTrend = Order::whereIn('id', $orderIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Recent Orders
            $recentOrders = Order::whereIn('id', $orderIds)
                ->with(['items'])
                ->latest()
                ->take(10)
                ->get();

            // ✅ Top Products (by Sales/Orders)
            $topProducts = \App\Models\OrderItem::where('provider_id', $provider->id)
                ->select('product_id', DB::raw('COUNT(*) as sales_count'))
                ->groupBy('product_id')
                ->with('product')
                ->orderByDesc('sales_count')
                ->limit(5)
                ->get();
        }

        // 3. Check-ins (Service Providers only)
        $checkinHistory = QrScan::whereIn('booking_id', function ($query) use ($serviceIds) {
            $query->select('id')->from('bookings')->whereIn('service_id', $serviceIds);
        })->with(['booking.traveler', 'booking.service'])->latest('scanned_at')->take(20)->get();

        // 4. AI Usage
        $aiUsage = $aiLimit->getUsage($provider);

        return view('provider.dashboard', compact(
            'provider',
            'isProductProvider',
            'totalServices',
            'totalProducts',
            'totalBookings',
            'pendingBookings',
            'recentBookings',
            'bookingsTrend',
            'topServices',
            'topProducts',        // ✅ नयाँ थपियो
            'recentOrders',
            'ordersTrend',
            'checkinHistory',
            'aiUsage'
        ));
    }
        /**
     * REALTIME-INCONSISTENCY-01: Get live counts for badges without page reload.
     */
    public function getLiveAlertCounts()
    {
        $user = Auth::user();
        $provider = $user->getCurrentProvider();

        if (!$provider) {
            return response()->json(['sos' => 0, 'quotations' => 0, 'orders' => 0]);
        }

        try {
            // 1. Pending SOS Alerts
            $unreadSos = \App\Models\SosAlert::where('provider_id', $provider->id)
                ->where('status', 'pending')
                ->count();

            // 2. Pending Quotation Requests
            $pendingQuotations = \App\Models\QuotationRequest::where('provider_id', $provider->id)
                ->where('status', 'pending')
                ->count();

            // 3. Pending Orders (correct column: provider_status)
            $pendingOrders = \App\Models\OrderItem::where('provider_id', $provider->id)
                ->where('provider_status', 'pending')
                ->count();

            return response()->json([
                'sos' => $unreadSos,
                'quotations' => $pendingQuotations,
                'orders' => $pendingOrders,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getLiveAlertCounts failed', ['provider_id' => $provider->id, 'error' => $e->getMessage()]);
            return response()->json(['sos' => 0, 'quotations' => 0, 'orders' => 0], 200);
        }
    }
    }