<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use App\Models\CustomOrder;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtCraftDashboardController extends Controller
{
    public function index()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->where('slug', 'art-craft'))
            ->first();
            
        abort_unless($provider, 403, 'Art/Craft provider profile not found.');

        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // KPIs
        $totalArtworks = Artwork::where('provider_id', $provider->id)->count();
        
        $soldCount = Artwork::where('provider_id', $provider->id)
            ->where('status', 'sold')
            ->where('updated_at', '>=', $monthStart)
            ->count();

        $pendingOrders = CustomOrder::where('provider_id', $provider->id)
            ->where('status', 'pending')->count();

        $totalRevenue = (float) Artwork::where('provider_id', $provider->id)
            ->where('status', 'sold')
            ->where('updated_at', '>=', $monthStart)
            ->sum('price');

        // Chart Data (Last 7 days)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        
        $salesRaw = Artwork::where('provider_id', $provider->id)
            ->where('status', 'sold')
            ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(updated_at) as d, SUM(price) as v')
            ->groupBy('d')->pluck('v', 'd');

        $ordersRaw = CustomOrder::where('provider_id', $provider->id)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, SUM(budget) as v')
            ->groupBy('d')->pluck('v', 'd');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'sales' => (int) ($salesRaw[$date] ?? 0),
                'custom' => (int) ($ordersRaw[$date] ?? 0),
            ];
        }

        // Fetch Artworks & Orders
        $artworks = Artwork::where('provider_id', $provider->id)->latest()->get();
        $orders = CustomOrder::where('provider_id', $provider->id)->latest()->get();

        // Profile Stats
        $artistName = $provider->name;
        $completedWorks = $totalArtworks; // कुल आर्टवर्क संख्या
        $rating = 4.9;
        $clients = \App\Models\CustomOrder::where('provider_id', $provider->id)
                    ->distinct('client_email')->count('client_email'); // कुल क्लाइन्ट संख्या

        return view('provider.art-craft.dashboard', compact(
            'provider', 'totalArtworks', 'soldCount', 'pendingOrders', 'totalRevenue',
            'chartData', 'artworks', 'orders', 'artistName', 'completedWorks', 'rating', 'clients'
        ));
    }

    public function updateArtworkStatus(Request $request, Artwork $artwork)
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider && $artwork->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:available,sold,custom',
        ]);

        $artwork->status = $validated['status'];
        $artwork->save();

        return response()->json(['success' => true, 'status' => $artwork->status]);
    }

    public function updateOrderStatus(Request $request, CustomOrder $order)
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,progress,done',
        ]);

        $order->status = $validated['status'];
        $order->save();

        return response()->json(['success' => true, 'status' => $order->status]);
    }
}