<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trek;
use App\Models\Agency;
use App\Models\Trekker;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $agency = Auth::guard('agency')->user();

        // ========================================================
        // 🔥 REDIRECT: यदि agency को user_id छ र User provider_owner हो भने provider dashboard मा पठाऊ
        // ========================================================
        if ($agency->user_id) {
            $user = User::find($agency->user_id);
            if ($user && $user->isProviderOwner()) {
                return redirect()->route('provider.dashboard')
                    ->with('info', 'Welcome to your new Provider Dashboard!');
            }
        }

        // ========================================================
        // SUPER ADMIN DASHBOARD
        // ========================================================
        if ($agency->role === 'super_admin') {
            // Basic stats (new structure)
            $totalTreks = Trek::count();
            $totalBookings = Booking::count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $totalAgencies = Agency::count();
            $totalTrekkers = Trekker::count();
            $todayBookings = Booking::whereDate('start_date', today())->count();

            // Recent bookings with traveler & service
            $recentBookings = Booking::with(['traveler', 'service'])
                ->latest()
                ->take(5)
                ->get();

            // Agencies with treks count (bookings count via services)
            $agencies = Agency::withCount('treks')->get();
            foreach ($agencies as $agt) {
                // Get all service_ids from this agency's treks
                $serviceIds = Trek::where('agency_id', $agt->id)
                    ->whereNotNull('service_id')
                    ->pluck('service_id');
                $agt->bookings_count = Booking::whereIn('service_id', $serviceIds)->count();
            }

            // Top 5 Treks by bookings (via service)
            $topTreks = Trek::withCount(['bookings' => function($q) {
                $q->whereHas('service', function($sq) {
                    // no extra filter needed
                });
            }])->orderBy('bookings_count', 'desc')->take(5)->get();

            // Bookings trend (last 30 days)
            $bookingsTrend = Booking::selectRaw('DATE(start_date) as date, COUNT(*) as total')
                ->where('start_date', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Recent activities (simplified: merge recent bookings and agencies)
            $recentBookingsForActivity = Booking::with(['traveler', 'service'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($b) {
                    return (object) [
                        'type' => 'booking',
                        'description' => ($b->traveler->name ?? 'Guest') . ' booked ' . ($b->service->name ?? 'Service'),
                        'created_at' => $b->created_at,
                    ];
                });

            $recentAgencies = Agency::latest()
                ->take(5)
                ->get()
                ->map(function ($a) {
                    return (object) [
                        'type' => 'agency_registered',
                        'description' => $a->name . ' registered as agency',
                        'created_at' => $a->created_at,
                    ];
                });

            $recentActivities = $recentBookingsForActivity->concat($recentAgencies)
                ->sortByDesc('created_at')
                ->take(10);

            return view('agency.dashboard', compact(
                'totalTreks',
                'totalBookings',
                'pendingBookings',
                'totalAgencies',
                'totalTrekkers',
                'todayBookings',
                'recentBookings',
                'agencies',
                'topTreks',
                'bookingsTrend',
                'recentActivities',
                'agency'
            ));
        }

        // ========================================================
        // REGULAR AGENCY DASHBOARD (सामान्य एजेन्सी)
        // ========================================================
        $totalTreks = $agency->treks()->count();

        // Get all service_ids from this agency's treks
        $serviceIds = $agency->treks()->whereNotNull('service_id')->pluck('service_id');

        // Total bookings via those services
        $totalBookings = Booking::whereIn('service_id', $serviceIds)->count();

        // Pending bookings
        $pendingBookings = Booking::whereIn('service_id', $serviceIds)
            ->where('status', 'pending')
            ->count();

        // Recent bookings (5)
        $recentBookings = Booking::whereIn('service_id', $serviceIds)
            ->with(['traveler', 'service'])
            ->latest()
            ->take(5)
            ->get();

        return view('agency.dashboard', compact(
            'totalTreks',
            'totalBookings',
            'recentBookings',
            'pendingBookings',
            'agency'
        ));
    }

    /**
     * Export bookings (super admin only)
     */
    public function exportBookings()
    {
        if (Auth::guard('agency')->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        $bookings = Booking::with(['traveler', 'service', 'service.provider'])->get();

        // For now just return JSON, but you can implement Excel export later
        return response()->json($bookings);
    }
}