<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trek;
use App\Models\Agency;
use App\Models\Trekker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $agency = Auth::guard('agency')->user();

        if ($agency->role === 'super_admin') {
            // --- Super Admin Dashboard ---
            $totalTreks = Trek::count();
            $totalBookings = Booking::count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $totalAgencies = Agency::count();
            $totalTrekkers = Trekker::count();
            $todayBookings = Booking::whereDate('start_date', today())->count();

            // हालैका ५ बुकिङ्ग
            $recentBookings = Booking::with(['trekker', 'trek'])
                ->latest()
                ->take(5)
                ->get();

            // सबै एजेन्सीको तथ्याङ्क
            $agencies = Agency::withCount(['treks', 'bookings'])->get();

            // Top 5 Treks (सबैभन्दा धेरै बुकिङ्ग भएका)
            $topTreks = Trek::withCount('bookings')
                ->orderBy('bookings_count', 'desc')
                ->take(5)
                ->get();

            // पछिल्लो ३० दिनको बुकिङ्ग ट्रेन्ड (प्रति दिन)
            $bookingsTrend = Booking::selectRaw('DATE(start_date) as date, COUNT(*) as total')
                ->where('start_date', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // हालैका गतिविधिहरू (यहाँ हामी बुकिङ्ग र एजेन्सी सिर्जनालाई गतिविधि मान्छौं, तर यदि छुट्टै activity log छैन भने, हामी पछिल्ला बुकिङ्ग र नयाँ एजेन्सीहरू मिलाउँछौं)
            $recentActivities = collect();

            // हालैका बुकिङ्ग (अन्तिम ५)
            $recentBookingsForActivity = Booking::with('trekker', 'trek')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($b) {
                    return (object) [
                        'type' => 'booking',
                        'description' => $b->trekker->name . ' booked ' . $b->trek->name,
                        'created_at' => $b->created_at,
                    ];
                });

            // हालैका एजेन्सीहरू (अन्तिम ५)
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

            // मर्ज गरेर मिति अनुसार क्रमबद्ध
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

        // --- सामान्य एजेन्सीको लागि (पहिलेको जस्तै) ---
        $totalTreks = $agency->treks()->count();

        $totalBookings = $agency->treks()
            ->withCount('bookings')
            ->get()
            ->sum('bookings_count');

        $recentBookings = Booking::whereHas('trek', function ($query) use ($agency) {
            $query->where('agency_id', $agency->id);
        })->with(['trekker', 'trek'])
          ->latest()
          ->take(5)
          ->get();

        $pendingBookings = Booking::whereHas('trek', function ($query) use ($agency) {
            $query->where('agency_id', $agency->id);
        })->where('status', 'pending')->count();

        return view('agency.dashboard', compact(
            'totalTreks',
            'totalBookings',
            'recentBookings',
            'pendingBookings',
            'agency'
        ));
    }
    public function exportBookings()
{
    // यदि super admin हो भने मात्र
    if (Auth::guard('agency')->user()->role !== 'super_admin') {
        abort(403, 'Unauthorized');
    }
    
    $bookings = Booking::with(['trekker', 'trek', 'trek.agency'])->get();
    
    // Excel वा CSV export गर्ने कोड
    // (Laravel Excel package प्रयोग गर्न सक्नुहुन्छ)
    return response()->json($bookings); // अहिलेको लागि JSON
}
}