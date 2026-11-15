<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\TransportAiService;
use App\Models\TransportBooking;
use App\Models\TransportVehicle;
use App\Http\Controllers\Provider\Concerns\ManagesOffers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportDashboardController extends Controller
{
    use ManagesOffers;
    use \App\Traits\EnforcesPlanLimits;

    public function __construct(protected TransportAiService $transportAi) {}
    public function index()
    {
        $provider = Auth::user()->getCurrentProvider();
        if (!$provider || !$provider->types->contains('slug', 'transport-provider')) {
            $provider = null;
        }

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

// PHASE-2: topRoutes query
        $topRoutes = TransportBooking::where('provider_id', $provider->id)
            ->where('status', 'confirmed')
            ->selectRaw('route_name, SUM(amount) as total_revenue, COUNT(*) as trip_count')
            ->groupBy('route_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        return view('provider.transport.dashboard', compact('provider',
            'todayRevenue',
            'totalTripsToday',
            'activeFleet',
            'totalFleet',          // 🆕 थपियो — sidebar fleet count को लागि
            'pendingBookings',
            'chartData',
            'vehicles',
            'recentBookings', 'topRoutes'));
    }

    public function updateVehicleStatus(Request $request, TransportVehicle $vehicle)
    {
        $provider = Auth::user()->getCurrentProvider();
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
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $oldStatus = $booking->status;
        $booking->status = $validated['status'];
        $booking->save();

        // Seats inventory adjustment
        if ($booking->trip_id) {
            $trip = \App\Models\TransportTrip::lockForUpdate()->find($booking->trip_id);
            if ($trip) {
                $seats = $booking->booking_type === 'full_vehicle'
                    ? $trip->seats_total
                    : (int) $booking->passenger_count;

                if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
                    $trip->update([
                        'seats_available' => min($trip->seats_total, $trip->seats_available + $seats),
                    ]);
                } elseif ($validated['status'] !== 'cancelled' && $oldStatus === 'cancelled') {
                    $trip->update([
                        'seats_available' => max(0, $trip->seats_available - $seats),
                    ]);
                }
            }
        }

        // Dispatch confirmed email on transition to 'confirmed'
        if ($validated['status'] === 'confirmed' && $oldStatus !== 'confirmed' && $booking->passenger_email) {
            try {
                \Mail::to($booking->passenger_email)->queue(
                    new \App\Mail\TransportBookingConfirmedMail($booking)
                );
            } catch (\Throwable $e) {
                \Log::warning('Transport confirmed mail failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'status' => $booking->status]);
    }
        public function verifyPayment(TransportBooking $booking)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        if ($booking->payment_status === 'paid') {
            return back()->with('info', 'Payment already verified.');
        }

        $booking->payment_status = 'paid';
        $booking->paid_at = now();
        $booking->save();

        return back()->with('success', 'Payment verified successfully.');
    }
        public function verifyPaymentProof(Request $request, TransportBooking $booking)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        if ($booking->payment_status !== 'submitted') {
            return back()->withErrors(['payment' => 'No payment proof to verify. Current status: ' . $booking->payment_status]);
        }

        \DB::transaction(function () use ($booking) {
            $oldStatus = $booking->status;

            $booking->update([
                'payment_status' => 'paid',
                'paid_at'        => now(),
                'status'         => 'confirmed', // auto-confirm on verify
            ]);

            // Dispatch confirmed email if transitioning
            if ($oldStatus !== 'confirmed' && $booking->passenger_email) {
                try {
                    \Mail::to($booking->passenger_email)->queue(
                        new \App\Mail\TransportBookingConfirmedMail($booking)
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Confirmed email failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
                }
            }
        });

        return back()->with('success', '✅ Payment verified. Booking confirmed. Traveler notified.');
    }

    public function rejectPaymentProof(Request $request, TransportBooking $booking)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        if ($booking->payment_status !== 'submitted') {
            return back()->withErrors(['payment' => 'No submitted proof to reject.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $booking->update([
            'payment_status' => 'pending',
            'payment_ref'    => null,
            'payment_proof'  => null,
            'payment_submitted_at' => null,
        ]);

        // Notify traveler (bell)
        try {
            $traveler = \App\Models\User::where('email', $booking->passenger_email)->first();
            if ($traveler) {
                $traveler->notify(new \App\Notifications\PaymentRejectedNotification($booking, $validated['rejection_reason']));
            }
        } catch (\Throwable $e) {
            \Log::warning('Payment reject notify failed', ['id' => $booking->id]);
        }

        return back()->with('success', 'Payment proof rejected. Traveler notified to re-submit.');
    }

    public function storeVehicle(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        // Booking quota check (monthly limit)
        try {
            app(\App\Services\BookingLimitService::class)->reserve($provider);
        } catch (\DomainException $e) {
            return back()->withErrors(['limit' => __('messages.booking_limit_reached')])->withInput();
        }

        if ($redirect = $this->checkPlanLimit($provider, 'max_vehicles', \App\Models\TransportVehicle::class, 'vehicles')) {
            return $redirect;
        }

        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_type' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'nullable|string|max:100',
            'route_name' => 'nullable|string|max:150',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('transport-vehicles/' . $provider->id, 'public');
        }

        TransportVehicle::create([
            'provider_id' => $provider->id,
            'vehicle_number' => $validated['vehicle_number'],
            'vehicle_type' => $validated['vehicle_type'],
            'capacity' => $validated['capacity'],
            'driver_name' => $validated['driver_name'],
            'route_name' => $validated['route_name'],
            'image_path' => $imagePath,
            'status' => 'available',
        ]);

        return back()->with('success', __('messages.vehicle_created_success'));
    }

    public function destroyVehicle(TransportVehicle $vehicle)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $vehicle->provider_id === $provider->id, 403);

        $vehicle->delete();
        return back()->with('success', __('messages.vehicle_deleted_success'));
    }

    public function storeBooking(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:transport_vehicles,id',
            'passenger_name' => 'required|string|max:100',
            'passenger_email' => 'nullable|email|max:150',
            'route_name' => 'required|string|max:150',
            'travel_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        // TRANSPORT-TIME-OVERLAP: prevent double-booking same vehicle at same time
        $conflict = \DB::table('transport_bookings')
            ->where('provider_id', $provider->id)
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('travel_date', $validated['travel_date'])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('travel_time')
            ->where('travel_time', $validated['travel_time'] ?? null)
            ->exists();
        if ($conflict) {
            return back()->withErrors(['vehicle_id' => 'This vehicle is already booked for the selected date and time.'])->withInput();
        }


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
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        \DB::transaction(function () use ($booking) {
            if ($booking->trip_id && $booking->status !== 'cancelled') {
                $trip = \App\Models\TransportTrip::lockForUpdate()->find($booking->trip_id);
                if ($trip) {
                    $restore = $booking->booking_type === 'full_vehicle'
                        ? $trip->seats_total
                        : (int) $booking->passenger_count;
                    $trip->update([
                        'seats_available' => min($trip->seats_total, $trip->seats_available + $restore),
                    ]);
                }
            }
            $booking->delete();
        });

        return back()->with('success', __('messages.booking_deleted_success'));
    }

    public function bookingsIndex()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');
        $bookings = TransportBooking::where('provider_id', $provider->id)
            ->with('vehicle')->latest()->paginate(20);
        return view('provider.transport.bookings.index', compact('provider', 'bookings'));
    }

    public function showBooking(TransportBooking $booking)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);
        abort_unless((int)$booking->provider_id === (int)$provider->id, 403);
        $booking->load('vehicle', 'provider');
        $provider_layout = 'layouts.provider-transport';
        return view('provider.transport.bookings.show', compact('booking', 'provider', 'provider_layout'));
    }

    public function vehiclesPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');
        $vehicles = TransportVehicle::where('provider_id', $provider->id)->orderBy('vehicle_number')->get();

        $totalFleet = $vehicles->count();
        $activeFleet = $vehicles->whereIn('status', ['available', 'on-route'])->count();

        return view('provider.transport.vehicles', compact('provider', 'vehicles', 'activeFleet', 'totalFleet'));
    }

    public function routesPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');

        $routes = \App\Models\TransportRoute::with(['fromLocation:id,city,state', 'toLocation:id,city,state'])
            ->withCount('trips')
            ->where('provider_id', $provider->id)
            ->orderByDesc('created_at')
            ->get();

        $locations = \App\Models\Location::where('is_habitable', true)
            ->orderBy('city')
            ->get(['id', 'city', 'state']);

        $vehicles = TransportVehicle::where('provider_id', $provider->id)->get();
        $totalFleet = $vehicles->count();
        $activeFleet = $vehicles->whereIn('status', ['available', 'on-route'])->count();

        return view('provider.transport.routes', compact('provider', 'routes', 'locations', 'activeFleet', 'totalFleet'));
    }

    public function storeRoute(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id'   => 'required|exists:locations,id|different:from_location_id',
            'duration_minutes' => 'nullable|integer|min:0',
            'distance_km'      => 'nullable|numeric|min:0',
        ]);

        $exists = \App\Models\TransportRoute::where('provider_id', $provider->id)
            ->where('from_location_id', $validated['from_location_id'])
            ->where('to_location_id', $validated['to_location_id'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['from_location_id' => 'This route already exists for your account.'])->withInput();
        }

        $fromLoc = \App\Models\Location::find($validated['from_location_id']);
        $toLoc = \App\Models\Location::find($validated['to_location_id']);

        \App\Models\TransportRoute::create([
            'provider_id'      => $provider->id,
            'from_location_id' => $validated['from_location_id'],
            'to_location_id'   => $validated['to_location_id'],
            'name'             => $fromLoc->city . ' → ' . $toLoc->city,
            'duration_minutes' => $validated['duration_minutes'] ?? 0,
            'distance_km'      => $validated['distance_km'] ?? null,
            'is_active'        => true,
        ]);

        return back()->with('success', 'Route created successfully.');
    }

    public function updateRoute(Request $request, \App\Models\TransportRoute $route)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $route->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id'   => 'required|exists:locations,id|different:from_location_id',
            'duration_minutes' => 'nullable|integer|min:0',
            'distance_km'      => 'nullable|numeric|min:0',
            'is_active'        => 'nullable|boolean',
        ]);

        $fromLoc = \App\Models\Location::find($validated['from_location_id']);
        $toLoc = \App\Models\Location::find($validated['to_location_id']);

        $validated['name'] = $fromLoc->city . ' → ' . $toLoc->city;
        $validated['is_active'] = $request->boolean('is_active');

        $route->update($validated);

        return back()->with('success', 'Route updated successfully.');
    }

    public function destroyRoute(\App\Models\TransportRoute $route)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $route->provider_id === $provider->id, 403);

        if ($route->trips()->count() > 0) {
            return back()->withErrors(['route' => 'Cannot delete — this route has scheduled trips. Cancel/remove trips first.']);
        }

        $route->delete();
        return back()->with('success', 'Route deleted successfully.');
    }
    
    public function tripsPage(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $filterDate = $request->query('date', now()->format('Y-m-d'));
        $filterRoute = $request->query('route_id');
        $filterStatus = $request->query('status', 'scheduled');

        $query = \App\Models\TransportTrip::with([
                'vehicle:id,vehicle_number,vehicle_type,capacity',
                'route:id,name,from_location_id,to_location_id',
                'route.fromLocation:id,city',
                'route.toLocation:id,city',
            ])
            ->where('provider_id', $provider->id)
            ->orderBy('departure_date')
            ->orderBy('departure_time');

        if ($filterDate) $query->whereDate('departure_date', $filterDate);
        if ($filterRoute) $query->where('route_id', $filterRoute);
        if ($filterStatus) $query->where('status', $filterStatus);

        $trips = $query->get()->groupBy(fn($t) => $t->departure_date->format('Y-m-d'));

        $routes = \App\Models\TransportRoute::where('provider_id', $provider->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $vehicles = TransportVehicle::where('provider_id', $provider->id)
            ->whereIn('status', ['available', 'on-route'])
            ->orderBy('vehicle_number')
            ->get(['id', 'vehicle_number', 'vehicle_type', 'capacity', 'driver_name']);

        $tripsForJs = collect($trips)->flatten(1)->map(fn($t) => [
            'id' => $t->id,
            'vehicle_id' => $t->vehicle_id,
            'route_id' => $t->route_id,
            'departure_date' => $t->departure_date->format('Y-m-d'),
            'departure_time' => substr($t->departure_time, 0, 5),
            'price_per_seat' => (float) $t->price_per_seat,
            'price_per_vehicle' => $t->price_per_vehicle ? (float) $t->price_per_vehicle : null,
            'driver_name' => $t->driver_name,
            'pickup_point' => $t->pickup_point,
            'status' => $t->status,
            'seats_total' => $t->seats_total,
        ])->values();

        return view('provider.transport.trips', compact(
            'provider', 'trips', 'routes', 'vehicles',
            'filterDate', 'filterRoute', 'filterStatus', 'tripsForJs'
        ));
    }

    public function storeTrip(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:transport_vehicles,id',
            'route_id' => 'required|exists:transport_routes,id',
            'departure_date' => 'required|date|after_or_equal:today',
            'departure_time' => 'required|date_format:H:i',
            'price_per_seat' => 'required|numeric|min:0',
            'price_per_vehicle' => 'nullable|numeric|min:0',
            'driver_name' => 'nullable|string|max:100',
            'pickup_point' => 'nullable|string|max:255',
        ]);

        $vehicle = TransportVehicle::where('id', $validated['vehicle_id'])
            ->where('provider_id', $provider->id)->firstOrFail();
        $route = \App\Models\TransportRoute::where('id', $validated['route_id'])
            ->where('provider_id', $provider->id)->firstOrFail();

        $conflict = \App\Models\TransportTrip::where('vehicle_id', $vehicle->id)
            ->whereDate('departure_date', $validated['departure_date'])
            ->whereTime('departure_time', $validated['departure_time'])
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($conflict) {
            return back()->withErrors(['vehicle_id' => 'This vehicle already has a departure at this date & time.'])->withInput();
        }

        // Auto pickup from transport_details default
        $pickup = $validated['pickup_point'];
        if (empty($pickup)) {
            $service = \App\Models\Service::where('provider_id', $provider->id)
                ->whereHas('category', fn($q) => $q->where('slug', 'transport'))->first();
            $pickup = $service?->transportDetail?->pickup_point;
        }

        \App\Models\TransportTrip::create([
            'provider_id' => $provider->id,
            'vehicle_id' => $vehicle->id,
            'route_id' => $route->id,
            'departure_date' => $validated['departure_date'],
            'departure_time' => $validated['departure_time'],
            'price_per_seat' => $validated['price_per_seat'],
            'price_per_vehicle' => $validated['price_per_vehicle'] ?? null,
            'seats_total' => $vehicle->capacity,
            'seats_available' => $vehicle->capacity,
            'driver_name' => $validated['driver_name'] ?? $vehicle->driver_name,
            'pickup_point' => $pickup,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Departure created successfully.');
    }

    public function updateTrip(Request $request, \App\Models\TransportTrip $trip)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $trip->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:transport_vehicles,id',
            'route_id' => 'required|exists:transport_routes,id',
            'departure_date' => 'required|date',
            'departure_time' => 'required|date_format:H:i',
            'price_per_seat' => 'required|numeric|min:0',
            'price_per_vehicle' => 'nullable|numeric|min:0',
            'driver_name' => 'nullable|string|max:100',
            'pickup_point' => 'nullable|string|max:255',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);

        $vehicle = TransportVehicle::where('id', $validated['vehicle_id'])
            ->where('provider_id', $provider->id)->firstOrFail();
        $route = \App\Models\TransportRoute::where('id', $validated['route_id'])
            ->where('provider_id', $provider->id)->firstOrFail();

        $conflict = \App\Models\TransportTrip::where('vehicle_id', $vehicle->id)
            ->whereDate('departure_date', $validated['departure_date'])
            ->whereTime('departure_time', $validated['departure_time'])
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $trip->id)
            ->exists();
        if ($conflict) {
            return back()->withErrors(['vehicle_id' => 'This vehicle already has a departure at this date & time.'])->withInput();
        }

        $bookedSeats = (int) $trip->bookings()->where('status', '!=', 'cancelled')->sum('passenger_count');
        $newSeatsTotal = $vehicle->capacity;
        if ($newSeatsTotal < $bookedSeats) {
            return back()->withErrors(['vehicle_id' => "Cannot use a vehicle with fewer seats than already booked ($bookedSeats seats)."])->withInput();
        }

        $trip->update([
            'vehicle_id' => $vehicle->id,
            'route_id' => $route->id,
            'departure_date' => $validated['departure_date'],
            'departure_time' => $validated['departure_time'],
            'price_per_seat' => $validated['price_per_seat'],
            'price_per_vehicle' => $validated['price_per_vehicle'] ?? null,
            'seats_total' => $newSeatsTotal,
            'seats_available' => $newSeatsTotal - $bookedSeats,
            'driver_name' => $validated['driver_name'] ?? $trip->driver_name,
            'pickup_point' => $validated['pickup_point'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Departure updated successfully.');
    }

    public function destroyTrip(\App\Models\TransportTrip $trip)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $trip->provider_id === $provider->id, 403);

        $bookedCount = (int) $trip->bookings()->where('status', '!=', 'cancelled')->count();
        if ($bookedCount > 0) {
            return back()->withErrors(['trip' => "Cannot delete — $bookedCount booking(s) exist. Cancel this departure instead."]);
        }

        $trip->delete();
        return back()->with('success', 'Departure deleted successfully.');
    }

    public function driversPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');
        $vehicles = TransportVehicle::where('provider_id', $provider->id)->get();
        $drivers = $vehicles->whereNotNull('driver_name')->sortBy('driver_name');

        $totalFleet = $vehicles->count();
        $activeFleet = $vehicles->whereIn('status', ['available', 'on-route'])->count();

        return view('provider.transport.drivers', compact('provider', 'drivers', 'activeFleet', 'totalFleet'));
    }
        public function aiVehicleAssignment(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);
        return response()->json($this->transportAi->vehicleAssignment($provider));
    }

    public function aiCostEstimator(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);
        return response()->json($this->transportAi->costEstimator($provider));
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

    public function updateVehicle(Request $request, TransportVehicle $vehicle)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $vehicle->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:50',
            'vehicle_type'   => 'required|string|max:50',
            'capacity'       => 'required|integer|min:1',
            'driver_name'    => 'nullable|string|max:100',
            'route_name'     => 'nullable|string|max:150',
            'status'         => 'required|in:available,on-route,maintenance,charging',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $exists = TransportVehicle::where('provider_id', $provider->id)
            ->where('vehicle_number', $validated['vehicle_number'])
            ->where('id', '!=', $vehicle->id)->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['vehicle_number' => __('messages.vehicle_number_exists')])->withInput();
        }

        if ($request->hasFile('image')) {
            if ($vehicle->image_path) {
                \Storage::disk('public')->delete($vehicle->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('transport-vehicles/' . $provider->id, 'public');
        }
        unset($validated['image']);

        $vehicle->update($validated);
        return redirect()->back()->with('success', __('messages.vehicle_updated_success'));
    }
}
