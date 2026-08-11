<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Platform Stats
        $totalUsers = User::count();
        $totalProviders = Provider::count();
        $totalServices = Service::count();
        $totalBookings = Booking::count();

        // Revenue
        $totalRevenue = Payment::where('status', 'success')->sum('amount');

        // Revenue by month (last 12 months)
        $revenueByMonth = Payment::where('status', 'success')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Provider growth (last 12 months)
        $providerGrowth = Provider::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Booking status distribution
        $bookingStatus = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Top 5 providers by revenue
        $topProviders = Provider::withSum(['payments' => function ($q) {
            $q->where('status', 'success');
        }], 'amount')
            ->orderBy('payments_sum_amount', 'desc')
            ->limit(5)
            ->get();

        // Top 5 services by bookings
        $topServices = Service::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        // Recent activities (new providers, bookings, payments)
        $recentActivities = collect();

        $recentProviders = Provider::with('user')->latest()->limit(5)->get()
            ->map(function ($p) {
                return (object)[
                    'type' => 'provider_registered',
                    'description' => "New provider: {$p->name}",
                    'created_at' => $p->created_at,
                ];
            });

        $recentBookings = Booking::with(['traveler', 'service'])->latest()->limit(5)->get()
            ->map(function ($b) {
                return (object)[
                    'type' => 'booking_created',
                    'description' => "Booking #{$b->id} by " . ($b->traveler->name ?? 'Guest'),
                    'created_at' => $b->created_at,
                ];
            });

        $recentActivities = $recentProviders->concat($recentBookings)
            ->sortByDesc('created_at')
            ->take(10);

        return view('admin.analytics.index', compact(
            'totalUsers',
            'totalProviders',
            'totalServices',
            'totalBookings',
            'totalRevenue',
            'revenueByMonth',
            'providerGrowth',
            'bookingStatus',
            'topProviders',
            'topServices',
            'recentActivities'
        ));
    }
}