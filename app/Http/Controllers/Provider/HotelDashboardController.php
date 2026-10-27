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
                'occ' => (int) ($chartRaw[$date] ?? 0), // Mocking booked nights per day for chart
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
}