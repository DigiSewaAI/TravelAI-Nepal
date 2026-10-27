<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\TransportBooking;
use App\Models\TransportVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportDashboardController extends Controller
{
    public function index()
    {
        $provider = \App\Models\Provider::where('user_id', Auth::id())
    ->whereHas('types', fn($q) => $q->where('slug', 'transport-provider'))
    ->first();

abort_unless($provider, 403, 'Transport provider profile not found.');

        $today = now()->startOfDay();

        // KPIs
        $todayRevenue = (float) TransportBooking::where('provider_id', $provider->id)
            ->where('status', 'confirmed')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $totalTripsToday = TransportBooking::where('provider_id', $provider->id)
            ->whereDate('travel_date', $today)
            ->where('status', '!=', 'cancelled')
            ->count();

        $activeFleet = TransportVehicle::where('provider_id', $provider->id)
            ->whereIn('status', ['available', 'on-route'])
            ->count();
            
        $pendingBookings = TransportBooking::where('provider_id', $provider->id)
            ->where('status', 'pending')->count();

        // Chart (Last 7 days Trip Volume)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $chartRaw = TransportBooking::where('provider_id', $provider->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as v')
            ->groupBy('d')->pluck('v', 'd');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'trips' => (int) ($chartRaw[$date] ?? 0),
            ];
        }

        // Data Lists
        $vehicles = TransportVehicle::where('provider_id', $provider->id)->orderBy('vehicle_number')->get();
        $recentBookings = TransportBooking::with('vehicle')->where('provider_id', $provider->id)->latest()->limit(6)->get();

        return view('provider.transport.dashboard', compact(
            'provider', 'todayRevenue', 'totalTripsToday', 'activeFleet', 'pendingBookings',
            'chartData', 'vehicles', 'recentBookings'
        ));
    }

    public function updateVehicleStatus(Request $request, TransportVehicle $vehicle)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $vehicle->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:available,on-route,maintenance,charging',
        ]);

        $vehicle->status = $validated['status'];
        $vehicle->save();

        return response()->json(['success' => true, 'status' => $vehicle->status]);
    }

    public function updateBookingStatus(Request $request, TransportBooking $booking)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $booking->status = $validated['status'];
        $booking->save();

        return response()->json(['success' => true, 'status' => $booking->status]);
    }
}