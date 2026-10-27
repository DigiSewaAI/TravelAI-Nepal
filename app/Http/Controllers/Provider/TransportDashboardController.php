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
        public function storeVehicle(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'nullable|string|max:100',
            'route_name' => 'nullable|string|max:150',
        ]);

        TransportVehicle::create([
            'provider_id' => $provider->id,
            'vehicle_number' => $validated['vehicle_number'],
            'vehicle_type' => $validated['vehicle_type'],
            'capacity' => $validated['capacity'],
            'driver_name' => $validated['driver_name'],
            'route_name' => $validated['route_name'],
            'status' => 'available',
        ]);

        return back()->with('success', __('messages.vehicle_created_success'));
    }

    public function destroyVehicle(TransportVehicle $vehicle)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $vehicle->provider_id === $provider->id, 403);
        
        $vehicle->delete();
        return back()->with('success', __('messages.vehicle_deleted_success'));
    }

    public function storeBooking(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:transport_vehicles,id',
            'passenger_name' => 'required|string|max:100',
            'passenger_email' => 'nullable|email|max:150',
            'route_name' => 'required|string|max:150',
            'travel_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        TransportBooking::create([
            'provider_id' => $provider->id,
            'vehicle_id' => $validated['vehicle_id'],
            'passenger_name' => $validated['passenger_name'],
            'passenger_email' => $validated['passenger_email'] ?? null,
            'booking_ref' => 'TRP-' . strtoupper(uniqid()),
            'route_name' => $validated['route_name'],
            'travel_date' => $validated['travel_date'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return back()->with('success', __('messages.booking_created_success'));
    }

    public function destroyBooking(TransportBooking $booking)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        $booking->delete();
        return back()->with('success', __('messages.booking_deleted_success'));
    }
}