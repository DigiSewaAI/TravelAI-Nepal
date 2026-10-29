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
        $reviewCount = \App\Models\Review::whereHas('service', function($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->count();

        // Chart (Last 7 days earnings)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));

        $chartRaw = IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed'])
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
            ->where('start_date', '>=', now()->subMonth())
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

                $topServiceTypes = IndividualBooking::where('provider_id', $provider->id)

                    ->whereIn('status', ['completed', 'confirmed'])

                    ->selectRaw('service_type, SUM(amount) as total_earnings, COUNT(*) as booking_count')

                    ->groupBy('service_type')

                    ->orderByDesc('total_earnings')

                    ->limit(5)

                    ->get();


        return view('provider.individual.dashboard', compact('provider', 'totalEarnings', 'bookingsThisMonth', 'profileViews',
            'avgRating', 'reviewCount', 'chartData', 'assignments', 'topServiceTypes'));
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

    public function storeAssignment(Request $request)
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))
            ->first();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'client_name' => 'required|string|max:100',
            'service_type' => 'required|in:guide,porter,photographer',
            'start_date' => 'required|date',
            'duration' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
        ]);

        IndividualBooking::create([
            'provider_id' => $provider->id,
            'service_type' => $validated['service_type'],
            'client_name' => $validated['client_name'],
            'title' => $validated['title'],
            'pax' => 1,
            'start_date' => $validated['start_date'],
            'duration' => $validated['duration'],
            'amount' => $validated['amount'],
            'status' => 'pending',
        ]);

        return back()->with('success', __('messages.assignment_created_success'));
    }

    public function destroyAssignment(IndividualBooking $booking)
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider && $booking->provider_id === $provider->id, 403);

        $booking->delete();
        return back()->with('success', __('messages.assignment_deleted_success'));
    }

    public function bookingsPage()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))
            ->first();
        abort_unless($provider, 403, 'Provider not found.');

        $bookings = IndividualBooking::where('provider_id', $provider->id)
            ->orderByDesc('start_date')
            ->get();

        return view('provider.individual.bookings', compact('provider', 'bookings'));
    }

    public function calendarPage()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))
            ->first();
        abort_unless($provider, 403, 'Provider not found.');

        $assignments = IndividualBooking::where('provider_id', $provider->id)->get();

        return view('provider.individual.calendar', compact('provider', 'assignments'));
    }

    public function earningsPage()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))
            ->first();
        abort_unless($provider, 403, 'Provider not found.');

        $totalEarnings = IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed'])
            ->sum('amount');

        $completedJobs = IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed'])
            ->count();

        // 🆕 Recent earning jobs
        $recentJobs = IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed'])
            ->orderByDesc('start_date')
            ->limit(10)
            ->get();

        // 🆕 Last 7 days earnings chart
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $chartRaw = IndividualBooking::where('provider_id', $provider->id)
            ->whereIn('status', ['completed', 'confirmed'])
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

        return view('provider.individual.earnings', compact(
            'provider', 'totalEarnings', 'completedJobs', 'recentJobs', 'chartData'
        ));
    }

    public function reviewsPage()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->whereIn('slug', ['guide', 'porter', 'photographer']))
            ->first();
        abort_unless($provider, 403, 'Provider not found.');

        $serviceIds = $provider->services()->pluck('id');

        $reviewsRaw = \App\Models\Review::whereIn('service_id', $serviceIds)
            ->latest()
            ->take(10)
            ->get();

        // ग्राहकको नाम मिलाउने (UI dummy mapping — Phase 1)
        $clientNames = ['Emily Watson', 'Arjun Sharma', 'Maria Garcia', 'Rohan Kapoor', 'Priya Nair'];

        $reviews = $reviewsRaw->map(function ($r, $index) use ($clientNames) {
            $r->client_name = $clientNames[$index % count($clientNames)];
            return $r;
        });

        return view('provider.individual.reviews', compact('provider', 'reviews'));
    }

    public function updateAssignment(Request $request, IndividualBooking $assignment)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $assignment->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'title'        => 'required|string|max:150',
            'client_name'  => 'required|string|max:100',
            'service_type' => 'required|in:guide,porter,photographer',
            'start_date'   => 'required|date',
            'duration'     => 'required|string|max:50',
            'amount'       => 'required|numeric|min:0',
            'status'       => 'required|in:pending,confirmed,completed',
        ]);

        $assignment->update($validated);

        return redirect()->back()->with('success', __('messages.assignment_updated'));
    }
}
