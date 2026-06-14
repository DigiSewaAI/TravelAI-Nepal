<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // कुनै __construct() छैन – मिडलवेयर रूट मार्फत लागू हुन्छ

    public function index()
    {
        $agency = Auth::guard('agency')->user();

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
}