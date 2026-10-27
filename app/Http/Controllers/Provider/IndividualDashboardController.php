<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\IndividualBooking;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndividualDashboardController extends Controller
{
    public function index()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))
            ->first();
            
        abort_unless($provider, 403, 'Individual provider profile not found.');

        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // KPIs
        $totalEarnings = (float) IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed'])
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $bookingsThisMonth = IndividualBooking::where('provider_id', $provider->id)
            ->where('created_at', '>=', $monthStart)->count();

        $profileViews = $provider->profile_views ?? 0;
        
        // Average Rating (Hardcoded for Phase 1, as reviews table uses service_id)
        $avgRating = 4.9; 
        $reviewCount = 127;

        // Chart (Last 7 days earnings)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        
        $chartRaw = IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed']) // ✅ यहाँ फिक्स गरियो
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(amount) as v')
            ->groupBy('d')->pluck('v', 'd');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'v' => (float) ($chartRaw[$date] ?? 0),
            ];
        }

        // Calendar & Upcoming Assignments
        $assignments = IndividualBooking::where('provider_id', $provider->id)
            ->where('start_date', '>=', now()->subMonth()) // include recent past
            ->orderBy('start_date')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'type' => $b->service_type,
                'title' => $b->title,
                'client' => $b->client_name,
                'pax' => $b->pax,
                'duration' => $b->duration,
                'amount' => (float) $b->amount,
                'status' => $b->status,
                'dateKey' => $b->start_date->format('Y-m-d'),
                'dateStr' => $b->start_date->format('M d, Y'),
            ]);

        return view('provider.individual.dashboard', compact(
            'provider', 'totalEarnings', 'bookingsThisMonth', 'profileViews',
            'avgRating', 'reviewCount', 'chartData', 'assignments'
        ));
    }

    public function updateBookingStatus(Request $request, IndividualBooking $booking)
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed',
        ]);

        $booking->status = $validated['status'];
        $booking->save();

        return response()->json(['success' => true, 'status' => $booking->status]);
    }
}