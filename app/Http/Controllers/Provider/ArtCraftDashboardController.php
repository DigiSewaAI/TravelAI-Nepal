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
        public function storeArtwork(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'artist_name' => 'nullable|string|max:100',
            'category' => 'required|in:painting,sculpture,handicraft',
            'price' => 'required|numeric|min:0',
            'emoji' => 'nullable|string|max:10',
        ]);

        Artwork::create([
            'provider_id' => $provider->id,
            'name' => $validated['name'],
            'artist_name' => $validated['artist_name'] ?? $provider->name,
            'category' => $validated['category'],
            'price' => $validated['price'],
            'emoji' => $validated['emoji'] ?? '🖼️',
            'gradient' => 'linear-gradient(135deg,#f9dad0,#c65d3b)', // Default gradient
            'status' => 'available',
        ]);

        return back()->with('success', __('messages.artwork_created_success'));
    }

    public function destroyArtwork(Artwork $artwork)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $artwork->provider_id === $provider->id, 403);

        $artwork->delete();
        return back()->with('success', __('messages.artwork_deleted_success'));
    }

    public function storeCustomOrder(Request $request)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'client_name' => 'required|string|max:100',
            'client_email' => 'nullable|email|max:150',
            'type' => 'required|string|max:50',
            'budget' => 'required|numeric|min:0',
            'deadline' => 'required|date',
        ]);

        CustomOrder::create([
            'provider_id' => $provider->id,
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'] ?? null,
            'type' => $validated['type'],
            'budget' => $validated['budget'],
            'deadline' => $validated['deadline'],
            'status' => 'pending',
        ]);

        return back()->with('success', __('messages.order_created_success'));
    }

    public function destroyOrder(CustomOrder $order)
    {
        $provider = Auth::user()->ownProvider();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        $order->delete();
        return back()->with('success', __('messages.order_deleted_success'));
    }
}