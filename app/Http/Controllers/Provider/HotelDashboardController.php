<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\HotelBooking;
use App\Models\HotelRoom;
use App\Models\HousekeepingTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelDashboardController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $today = now()->startOfDay();

        // KPIs
        $todayRevenue = (float) HotelBooking::where('provider_id', $provider->id)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $bookingsToday = HotelBooking::where('provider_id', $provider->id)
            ->where('created_at', '>=', $today)->count();

        $roomsOccupied = HotelRoom::where('provider_id', $provider->id)->where('status', 'occupied')->count();
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

        return view('provider.hotel.dashboard', compact(
            'provider', 'todayRevenue', 'bookingsToday', 'roomsOccupied', 'totalRooms',
            'guestsInHouse', 'chartData', 'frontDesk', 'rooms', 'tasks', 'recentBookings'
        ));
    }

    public function updateRoomStatus(Request $request, HotelRoom $room)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $room->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,dirty,maintenance',
        ]);

        $room->status = $validated['status'];
        $room->save();

        return response()->json(['success' => true, 'status' => $room->status]);
    }

    public function checkInOut(Request $request, HotelBooking $booking)
    {
        $provider = Auth::user()->ownProvider();
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

        return response()->json(['success' => true]);
    }

    public function storeBooking(Request $request)
    {
        $provider = Auth::user()->ownProvider();
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
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        if ($booking->room) {
            $booking->room->update(['status' => 'available']);
        }

        $booking->delete();
        return back()->with('success', __('messages.booking_deleted'));
    }
    public function bookingsPage()
{
    $provider = Auth::user()->ownProvider();
    $bookings = HotelBooking::with('room')->where('provider_id', $provider->id)->latest()->get();
    return view('provider.hotel.bookings', compact('provider', 'bookings'));
}

public function roomsPage()
{
    $provider = Auth::user()->ownProvider();
    $rooms = HotelRoom::where('provider_id', $provider->id)->orderBy('room_number')->get();
    return view('provider.hotel.rooms', compact('provider', 'rooms'));
}

public function guestsPage()
{
    $provider = Auth::user()->ownProvider();
    $guests = HotelBooking::with('room')->where('provider_id', $provider->id)->where('status', 'checked_in')->get();
    return view('provider.hotel.guests', compact('provider', 'guests'));
}

public function housekeepingPage()
{
    $provider = Auth::user()->ownProvider();
    $tasks = HousekeepingTask::with('room')->where('provider_id', $provider->id)->latest()->get();
    return view('provider.hotel.housekeeping', compact('provider', 'tasks'));
}
    public function reportsPage()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $totalBookings = HotelBooking::where('provider_id', $provider->id)->count();
        $totalRevenue = (float) HotelBooking::where('provider_id', $provider->id)->sum('amount');

        $totalRooms = HotelRoom::where('provider_id', $provider->id)->count();
        $occupiedRooms = HotelRoom::where('provider_id', $provider->id)->where('status', 'occupied')->count();
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

    public function offersPage()
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $offers = collect([
            (object)['title' => 'Monsoon Special', 'discount' => '25% Off', 'valid_until' => now()->addDays(14), 'status' => 'active'],
            (object)['title' => 'Long Stay Discount', 'discount' => '15% Off', 'valid_until' => now()->addDays(30), 'status' => 'active'],
            (object)['title' => 'Weekend Getaway', 'discount' => '10% Off', 'valid_until' => now()->addDays(7), 'status' => 'inactive'],
        ]);

        return view('provider.hotel.offers', compact('provider', 'offers'));
    }
}