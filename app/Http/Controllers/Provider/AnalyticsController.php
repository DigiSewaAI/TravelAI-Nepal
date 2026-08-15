<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        // Service IDs for this provider
        $serviceIds = $provider->services()->pluck('id');

        // Total Revenue
        $totalRevenue = Payment::where('provider_id', $provider->id)
            ->where('status', 'success')
            ->sum('amount');

        // Revenue by month (last 12 months)
        $revenueByMonth = Payment::where('provider_id', $provider->id)
            ->where('status', 'success')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Bookings by status
        $bookingsByStatus = Booking::whereIn('service_id', $serviceIds)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Top services by bookings
        $topServices = Service::whereIn('id', $serviceIds)
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        // Recent bookings (last 10)
        $recentBookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['traveler', 'service'])
            ->latest()
            ->limit(10)
            ->get();

        // Monthly bookings trend (last 12 months)
        $bookingTrend = Booking::whereIn('service_id', $serviceIds)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Customer acquisition (unique travelers)
        $totalCustomers = Booking::whereIn('service_id', $serviceIds)
            ->distinct('traveler_id')
            ->count('traveler_id');

        // 🔥 Average booking value (join with services table)
        $avgBookingValue = Booking::whereIn('service_id', $serviceIds)
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->avg('services.price') ?? 0;

        // Conversion rate (views to bookings - approximated)
        $totalServices = $serviceIds->count();
        $totalBookings = Booking::whereIn('service_id', $serviceIds)->count();
        $conversionRate = $totalServices > 0 ? round(($totalBookings / $totalServices) * 100, 1) : 0;

        return view('provider.analytics.index', compact(
            'provider',
            'totalRevenue',
            'revenueByMonth',
            'bookingsByStatus',
            'topServices',
            'recentBookings',
            'bookingTrend',
            'totalCustomers',
            'avgBookingValue',
            'conversionRate',
            'totalBookings',
            'totalServices'
        ));
    }

    /**
     * Export analytics as CSV
     */
    public function export(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        $serviceIds = $provider->services()->pluck('id');

        $bookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['traveler', 'service'])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=analytics_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($bookings) {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, ['Booking ID', 'Traveler', 'Service', 'Start Date', 'Status', 'Price', 'Currency']); // ✅ Currency added

    foreach ($bookings as $booking) {
        fputcsv($handle, [
            $booking->id,
            $booking->traveler->name ?? 'Guest',
            $booking->service->name ?? 'N/A',
            $booking->start_date->format('Y-m-d'),
            $booking->status,
            $booking->service->price ?? 0,
            $booking->service->currency ?? 'USD', // ✅ Currency column
        ]);
    }
    fclose($handle);
};

        return response()->stream($callback, 200, $headers);
    }
}