<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\HousekeepingTask;
use App\Http\Controllers\Provider\Concerns\ManagesOffers;
use App\Services\HotelAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelDashboardController extends Controller
{
    use ManagesOffers;
    use \App\Traits\EnforcesPlanLimits;

    public function __construct(protected HotelAiService $hotelAi) {}
    public function index()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        // Booking quota check (monthly limit)
        try {
            app(\App\Services\BookingLimitService::class)->reserve($provider);
        } catch (\DomainException $e) {
            return back()->withErrors(['limit' => __('messages.booking_limit_reached')])->withInput();
        }

        $today = now()->startOfDay();

        // KPIs
        $todayRevenue = (float) HotelBooking::where('provider_id', $provider->id)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $bookingsToday = HotelBooking::where('provider_id', $provider->id)
            ->where('created_at', '>=', $today)->count();

        $roomsOccupied = HotelBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in_date', '<=', now()->format('Y-m-d'))
            ->where('check_out_date', '>=', now()->format('Y-m-d'))
            ->distinct()->pluck('room_id')->count();
        $totalRooms = HotelRoom::where('provider_id', $provider->id)->count();

        $guestsInHouse = HotelBooking::where('provider_id', $provider->id)
            ->where('status', 'checked_in')->count();

        // Chart (Last 7 days)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $chartRaw = HotelBooking::where('provider_id', $provider->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as v')
            ->groupBy('d')->pluck('v', 'd');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'occ' => (int) ($chartRaw[$date] ?? 0),
                'avail' => max(0, $totalRooms - (int) ($chartRaw[$date] ?? 0))
            ];
        }

        // Front Desk (Check-ins & Check-outs Today)
        $frontDesk = HotelBooking::with('room')
            ->where('provider_id', $provider->id)
            ->where(function($q) use ($today) {
                $q->whereDate('check_in_date', $today)
                  ->orWhereDate('check_out_date', $today);
            })
            ->whereIn('status', ['pending', 'checked_in'])
            ->get();

        // Rooms
        $rooms = HotelRoom::where('provider_id', $provider->id)->orderBy('room_number')->get();

        // Housekeeping
        $tasks = HousekeepingTask::with('room')->where('provider_id', $provider->id)
            ->where('status', '!=', 'done')
            ->latest()->limit(6)->get();

        // Recent Bookings
        $recentBookings = HotelBooking::with('room')
            ->where('provider_id', $provider->id)
            ->latest()->limit(6)->get();

// PHASE-2: topRooms query
        $topRooms = HotelBooking::where('provider_id', $provider->id)
            ->where('payment_status', 'paid')
            ->selectRaw('room_id, SUM(amount) as total_revenue, COUNT(*) as booking_count')
            ->groupBy('room_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->with('room')
            ->get();

        return view('provider.hotel.dashboard', compact('provider', 'todayRevenue', 'bookingsToday', 'roomsOccupied', 'totalRooms',
            'guestsInHouse', 'chartData', 'frontDesk', 'rooms', 'tasks', 'recentBookings', 'topRooms'));
    }

    public function updateRoomStatus(Request $request, HotelRoom $room)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $room->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,dirty,maintenance',
        ]);

        $room->status = $validated['status'];
        // Auto-calculate min/max if base_price changes
        if (isset($validated['base_price'])) {
            $validated['min_price'] = $validated['min_price'] ?? round((float) $validated['base_price'] * 0.7, 2);
            $validated['max_price'] = $validated['max_price'] ?? round((float) $validated['base_price'] * 1.5, 2);
        }

        $room->save();

        return response()->json(['success' => true, 'status' => $room->status]);
    }

    public function checkInOut(Request $request, HotelBooking $booking)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'action' => 'required|in:check_in,check_out',
        ]);

        DB::transaction(function () use ($booking, $validated) {
            if ($validated['action'] === 'check_in') {
                $booking->update(['status' => 'checked_in']);
                $booking->room->update(['status' => 'occupied']);
            } else {
                $booking->update(['status' => 'checked_out']);
                $booking->room->update(['status' => 'dirty']);
            }
        });

        // AJAX request → JSON; normal form → redirect with message
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        $message = $validated['action'] === 'check_in'
            ? 'Guest checked in successfully.'
            : 'Guest checked out successfully.';

        return back()->with('success', $message);
    }

    public function storeBooking(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'room_id'        => 'required|exists:hotel_rooms,id',
            'guest_name'     => 'required|string|max:100',
            'guest_email'    => 'nullable|email|max:150',
            'check_in_date'  => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'amount'         => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,pending,refunded',
        ]);

        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        HotelBooking::create([
            'provider_id'    => $provider->id,
            'room_id'        => $validated['room_id'],
            'guest_name'     => $validated['guest_name'],
            'guest_email'    => $validated['guest_email'] ?? null,
            'check_in_date'  => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'nights'         => $nights > 0 ? $nights : 1,
            'amount'         => $validated['amount'],
            'payment_status' => $validated['payment_status'],
            'status'         => 'pending',
        ]);

        HotelRoom::where('id', $validated['room_id'])->update(['status' => 'reserved']);

        return back()->with('success', __('messages.booking_created'));
    }

    public function destroyBooking(HotelBooking $booking)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        if ($booking->room) {
            $booking->room->update(['status' => 'available']);
        }

        $booking->delete();
        return back()->with('success', __('messages.booking_deleted'));
    }
    public function bookingsPage()
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403, 'Provider not found.');
    $bookings = HotelBooking::with('room')->where('provider_id', $provider->id)->latest()->get();

    // Public bookings via service page (bookings table — different model)
    $serviceIds = \DB::table('services')->where('provider_id', $provider->id)->pluck('id');
    $publicBookings = \App\Models\Booking::with(['service', 'traveler'])
        ->whereIn('service_id', $serviceIds)
        ->latest()->get();

    return view('provider.hotel.bookings', compact('provider', 'bookings', 'publicBookings'));
}

public function showBooking(HotelBooking $booking)
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403, 'Provider not found.');
    abort_unless((int)$booking->provider_id === (int)$provider->id, 403);
    $booking->load('room', 'provider', 'folioCharges');
    $provider_layout = 'layouts.provider-hotel';
    return view('provider.hotel.bookings.show', compact('booking', 'provider', 'provider_layout'));
}

public function addFolioCharge(Request $request, HotelBooking $booking)
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403);
    abort_unless((int)$booking->provider_id === (int)$provider->id, 403);

    $validated = $request->validate([
        'description' => 'required|string|max:255',
        'category'    => 'required|in:room,food,laundry,extra,other',
        'amount'      => 'required|numeric|min:0',
        'quantity'    => 'nullable|integer|min:1|max:999',
        'notes'       => 'nullable|string|max:1000',
    ]);

    $qty = (int) ($validated['quantity'] ?? 1);
    \App\Models\FolioCharge::create([
        'hotel_booking_id' => $booking->id,
        'description'      => $validated['description'],
        'category'         => $validated['category'],
        'amount'           => $validated['amount'],
        'quantity'         => $qty,
        'total'            => $validated['amount'] * $qty,
        'notes'            => $validated['notes'] ?? null,
        'added_by'         => Auth::id(),
    ]);

    return back()->with('success', 'Charge added to folio.');
}

public function removeFolioCharge(HotelBooking $booking, \App\Models\FolioCharge $charge)
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403);
    abort_unless((int)$booking->provider_id === (int)$provider->id, 403);
    abort_unless((int)$charge->hotel_booking_id === (int)$booking->id, 403);

    $charge->delete();
    return back()->with('success', 'Charge removed.');
}

public function roomsPage()
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403, 'Provider not found.');
    $rooms = HotelRoom::where('provider_id', $provider->id)->orderBy('room_number')->get();
    return view('provider.hotel.rooms', compact('provider', 'rooms'));
}

public function guestsPage()
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403, 'Provider not found.');
    $guests = HotelBooking::with('room')->where('provider_id', $provider->id)->where('status', 'checked_in')->get();
    return view('provider.hotel.guests', compact('provider', 'guests'));
}

public function housekeepingPage()
{
    $provider = Auth::user()->getCurrentProvider();
    abort_unless($provider, 403);

    $tasks = HousekeepingTask::with('room')->where('provider_id', $provider->id)->latest()->get();
    $rooms = HotelRoom::where('provider_id', $provider->id)->orderBy('room_number')->get();
    $totalRooms = HotelRoom::where('provider_id', $provider->id)->count();
    $occupiedRooms = HotelRoom::where('provider_id', $provider->id)->where('status', 'occupied')->count();

    return view('provider.hotel.housekeeping', compact('provider', 'tasks', 'rooms', 'totalRooms', 'occupiedRooms'));
}
    public function aiOccupancyForecast(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $result = $this->hotelAi->occupancyForecast($provider, 7);

        return response()->json($result);
    }

    public function aiPricingSuggestions(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $result = $this->hotelAi->pricingSuggestions($provider);

        return response()->json($result);
    }
    public function applyPricing(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $v = $request->validate([
            'room_number' => 'required|string|max:50',
            'suggested_rate' => 'required|numeric|min:1000',
        ]);

        // ChatGPT fix — server-side min/max clamp
        $room = DB::table('hotel_rooms')->where('provider_id', $provider->id)
            ->where('room_number', $v['room_number'])->first();
        if (!$room) return back()->with('error', "Room {$v['room_number']} not found.");
        $rate = (float) $v['suggested_rate'];
        if ($room->min_price && $rate < $room->min_price) {
            return back()->with('error', "Suggested Rs " . number_format($rate) . " below min Rs " . number_format($room->min_price) . " for room {$v['room_number']}.");
        }
        if ($room->max_price && $rate > $room->max_price) {
            return back()->with('error', "Suggested Rs " . number_format($rate) . " above max Rs " . number_format($room->max_price) . " for room {$v['room_number']}.");
        }
        $n = DB::table('hotel_rooms')->where('provider_id', $provider->id)
            ->where('room_number', $v['room_number'])->update(['base_price' => $rate]);

        // PRICE-HISTORY-TABLE: log change if successful
        if ($n > 0 && $room) {
            DB::table('hotel_room_price_history')->insert([
                'room_id' => $room->id,
                'provider_id' => $provider->id,
                'old_price' => $room->base_price,
                'new_price' => $rate,
                'changed_by' => Auth::id(),
                'reason' => 'AI suggested rate applied',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($n === 0) return back()->with('error', "Room {$v['room_number']} not found.");
        return back()->with('success', "Room {$v['room_number']} base price updated to Rs " . number_format($v['suggested_rate']) . "/night.");
    }
    public function priceHistoryPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $history = \DB::table('hotel_room_price_history')
            ->join('hotel_rooms', 'hotel_rooms.id', '=', 'hotel_room_price_history.room_id')
            ->where('hotel_room_price_history.provider_id', $provider->id)
            ->leftJoin('users', 'users.id', '=', 'hotel_room_price_history.changed_by')
            ->select(
                'hotel_room_price_history.id',
                'hotel_room_price_history.old_price',
                'hotel_room_price_history.new_price',
                'hotel_room_price_history.reason',
                'hotel_room_price_history.created_at',
                'hotel_rooms.room_number',
                'hotel_rooms.room_type',
                'users.name as changed_by_name'
            )
            ->orderByDesc('hotel_room_price_history.created_at')
            ->limit(100)
            ->get();

        return view('provider.hotel.price-history', compact('provider', 'history'));
    }
    public function reportsPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $totalBookings = HotelBooking::where('provider_id', $provider->id)->where('status', '!=', 'cancelled')->count();
        $totalRevenue = (float) HotelBooking::where('provider_id', $provider->id)->where('status', '!=', 'cancelled')->sum('amount');

        $totalRooms = HotelRoom::where('provider_id', $provider->id)->count();
        $occupiedRooms = HotelBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('check_in_date', '<=', now()->format('Y-m-d'))
            ->where('check_out_date', '>=', now()->format('Y-m-d'))
            ->distinct()->pluck('room_id')->count();
        $occupancyRate = $totalRooms > 0 ? (int) round(($occupiedRooms / $totalRooms) * 100) : 0;

        // 7-day booking chart
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $chartRaw = HotelBooking::where('provider_id', $provider->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')->get()->keyBy('d');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'value' => (int) ($chartRaw[$date]->c ?? 0),
            ];
        }
        $chartTotal = collect($chartData)->sum('value');

        // Top 5 rooms by revenue — use relationship (safe, no FK guessing)
        $topRooms = HotelBooking::where('provider_id', $provider->id)
            ->where('status', '!=', 'cancelled')
            ->with('room')
            ->get()
            ->groupBy(fn($b) => $b->room->id ?? null)
            ->filter(fn($g, $k) => $k !== null)
            ->map(function($group) {
                $room = $group->first()->room;
                return (object)[
                    'number' => $room->room_number ?? '—',
                    'type' => $room->type ?? 'Room',
                    'revenue' => (float) $group->sum('amount'),
                    'bookings' => $group->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        return view('provider.hotel.reports', compact(
            'provider', 'totalBookings', 'totalRevenue',
            'totalRooms', 'occupiedRooms', 'occupancyRate',
            'chartData', 'chartTotal', 'topRooms'
        ));
    }

    public function storeRoom(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        if ($redirect = $this->checkPlanLimit($provider, 'max_rooms', \App\Models\HotelRoom::class, 'rooms')) {
            return $redirect;
        }

                $validated = $request->validate([
            'room_number' => 'required|string|max:20',
            'room_type'   => 'required|string|max:50',
            'floor'       => 'required|integer|min:1',
            'status'      => 'nullable|in:available,occupied,reserved,dirty,maintenance',
            'base_price'  => 'nullable|numeric|min:0',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'image'       => 'nullable|image|max:2048', // 🆕
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('hotel-rooms', 'public');
        }

        $exists = HotelRoom::where('provider_id', $provider->id)
            ->where('room_number', $validated['room_number'])
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['room_number' => 'This room number already exists.'])->withInput();
        }

        // Auto-calculate min/max from base_price if not provided
        if (!isset($validated['min_price']) && isset($validated['base_price'])) {
            $validated['min_price'] = round((float) $validated['base_price'] * 0.7, 2);
        }
        if (!isset($validated['max_price']) && isset($validated['base_price'])) {
            $validated['max_price'] = round((float) $validated['base_price'] * 1.5, 2);
        }

        HotelRoom::create([
            'provider_id' => $provider->id,
            'room_number' => $validated['room_number'],
            'room_type'   => $validated['room_type'],
            'floor'       => $validated['floor'],
            'status'      => $validated['status'] ?? 'available',
            'base_price'  => $validated['base_price'] ?? null,
            'image_path'  => $imagePath, // 🆕 Save Path
        ]);

        return redirect()->back()->with('success', 'Room added successfully.');
    }

    public function updateRoom(Request $request, HotelRoom $room)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $room->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'room_number' => 'required|string|max:20',
            'room_type'   => 'required|string|max:50',
            'floor'       => 'required|integer|min:1',
            'status'      => 'required|in:available,occupied,reserved,dirty,maintenance',
            'base_price'  => 'nullable|numeric|min:0',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'image'       => 'nullable|image|max:2048', // 🆕
        ]);

        $exists = HotelRoom::where('provider_id', $provider->id)
            ->where('room_number', $validated['room_number'])
            ->where('id', '!=', $room->id)
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['room_number' => 'This room number already exists.'])->withInput();
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($room->image_path && \Storage::disk('public')->exists($room->image_path)) {
                \Storage::disk('public')->delete($room->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('hotel-rooms', 'public');
        }

        $room->update($validated);

        return redirect()->back()->with('success', 'Room updated successfully.');
    }

    public function destroyRoom(HotelRoom $room)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $room->provider_id === $provider->id, 403);

        $activeBookings = HotelBooking::where('room_id', $room->id)
            ->whereIn('status', ['pending', 'checked_in'])->exists();
        if ($activeBookings) {
            return redirect()->back()->with('error', 'Cannot delete this room - it has active bookings.');
        }

        $room->delete();

        return redirect()->back()->with('success', 'Room deleted successfully.');
    }

    public function storeTask(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'task_name'   => 'required|string|max:100',
            'room_id'     => 'nullable|integer|exists:hotel_rooms,id',
            'assigned_to' => 'nullable|string|max:100',
            'status'      => 'nullable|in:queue,in_progress,done',
        ]);

        HousekeepingTask::create([
            'provider_id' => $provider->id,
            'room_id'     => $validated['room_id'] ?? null,
            'task_name'   => $validated['task_name'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'status'      => $validated['status'] ?? 'queue',
        ]);

        return redirect()->back()->with('success', 'Task added successfully.');
    }

    public function updateTaskStatus(Request $request, HousekeepingTask $task)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $task->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:queue,in_progress,done',
        ]);

        $task->status = $validated['status'];
        $task->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'status' => $task->status]);
        }

        return redirect()->back()->with('success', 'Task status updated.');
    }

    public function destroyTask(HousekeepingTask $task)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $task->provider_id === $provider->id, 403);

        $task->delete();

        return redirect()->back()->with('success', 'Task deleted successfully.');
    }
}