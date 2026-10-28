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

        $totalFleet = TransportVehicle::where('provider_id', $provider->id)->count();

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
            'provider',
            'todayRevenue',
            'totalTripsToday',
            'activeFleet',
            'totalFleet',          // 🆕 थपियो — sidebar fleet count को लागि
            'pendingBookings',
            'chartData',
            'vehicles',
            'recentBookings'
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

    public function vehiclesPage()
    {
        $provider = Auth::user()->ownProvider();
        $vehicles = TransportVehicle::where('provider_id', $provider->id)->orderBy('vehicle_number')->get();

        $totalFleet = $vehicles->count();
        $activeFleet = $vehicles->whereIn('status', ['available', 'on-route'])->count();

        return view('provider.transport.vehicles', compact('provider', 'vehicles', 'activeFleet', 'totalFleet'));
    }

    public function routesPage()
    {
        $provider = Auth::user()->ownProvider();
        $vehicles = TransportVehicle::where('provider_id', $provider->id)->get();
        $routes = $vehicles->whereNotNull('route_name')->groupBy('route_name');

        $totalFleet = $vehicles->count();
        $activeFleet = $vehicles->whereIn('status', ['available', 'on-route'])->count();

        return view('provider.transport.routes', compact('provider', 'routes', 'activeFleet', 'totalFleet'));
    }

    public function driversPage()
    {
        $provider = Auth::user()->ownProvider();
        $vehicles = TransportVehicle::where('provider_id', $provider->id)->get();
        $drivers = $vehicles->whereNotNull('driver_name')->sortBy('driver_name');

        $totalFleet = $vehicles->count();
        $activeFleet = $vehicles->whereIn('status', ['available', 'on-route'])->count();

        return view('provider.transport.drivers', compact('provider', 'drivers', 'activeFleet', 'totalFleet'));
    }
        public function reportsPage()
    {
        $provider = \App\Models\Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->where('slug', 'transport-provider'))
            ->first();
        abort_unless($provider, 403);

        $totalBookings = TransportBooking::where('provider_id', $provider->id)->count();
        $totalRevenue = (float) TransportBooking::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        $activeFleet = TransportVehicle::where('provider_id', $provider->id)
            ->whereIn('status', ['available', 'on-route'])->count();
        $totalFleet = TransportVehicle::where('provider_id', $provider->id)->count();

        // 7-day booking chart (matches index() pattern — uses created_at)
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
        $chartTotal = collect($chartData)->sum('trips');

        // Top 5 routes by revenue (group by route_name, amount)
        $topRoutes = TransportBooking::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('route_name, SUM(amount) as total_revenue, COUNT(*) as booking_count')
            ->groupBy('route_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn($row) => (object)[
                'name' => $row->route_name ?: '—',
                'revenue' => (float) $row->total_revenue,
                'bookings' => (int) $row->booking_count,
            ]);

        return view('provider.transport.reports', compact(
            'provider', 'totalBookings', 'totalRevenue',
            'activeFleet', 'totalFleet',
            'chartData', 'chartTotal', 'topRoutes'
        ));
    }

    public function offersPage()
    {
        $provider = \App\Models\Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->where('slug', 'transport-provider'))
            ->first();
        abort_unless($provider, 403);

        $activeFleet = TransportVehicle::where('provider_id', $provider->id)
            ->whereIn('status', ['available', 'on-route'])->count();
        $totalFleet = TransportVehicle::where('provider_id', $provider->id)->count();

        $offers = collect([
            (object)['title' => 'Dashain Travel Discount', 'discount' => '20% Off', 'valid_until' => now()->addDays(14), 'status' => 'active'],
            (object)['title' => 'Group Booking Special', 'discount' => '15% Off', 'valid_until' => now()->addDays(30), 'status' => 'active'],
            (object)['title' => 'Weekend Getaway Offer', 'discount' => '10% Off', 'valid_until' => now()->addDays(7), 'status' => 'inactive'],
        ]);

        return view('provider.transport.offers', compact('provider', 'offers', 'activeFleet', 'totalFleet'));
    }

    public function updateVehicle(Request $request, TransportVehicle $vehicle)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $vehicle->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_type'   => 'required|string|max:50',
            'capacity'       => 'required|integer|min:1',
            'driver_name'    => 'nullable|string|max:100',
            'route_name'     => 'nullable|string|max:150',
            'status'         => 'required|in:available,on-route,maintenance,charging',
        ]);

        $exists = TransportVehicle::where('provider_id', $provider->id)
            ->where('vehicle_number', $validated['vehicle_number'])
            ->where('id', '!=', $vehicle->id)->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['vehicle_number' => __('messages.vehicle_number_exists')])->withInput();
        }

        $vehicle->update($validated);
        return redirect()->back()->with('success', __('messages.vehicle_updated_success'));
    }
}
