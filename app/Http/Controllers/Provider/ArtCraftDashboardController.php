<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\ArtCraftAiService;
use App\Models\Artwork;
use App\Models\CustomOrder;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtCraftDashboardController extends Controller
{

    use \App\Traits\EnforcesPlanLimits;

    public function __construct(protected ArtCraftAiService $artcraftAi) {}

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

        // Profile Stats (sidebar artist card)
        $artistName = $provider->name;
        $completedWorks = $totalArtworks;
        $rating = 4.9;
        $clients = CustomOrder::where('provider_id', $provider->id)
            ->distinct('client_email')->count('client_email');

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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'status' => $artwork->status]);
        }

        return redirect()->back()->with('success', __('messages.updated_success'));
    }

    public function updateOrderStatus(Request $request, CustomOrder $order)
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,under_review,quoted,accepted,revision_requested,rejected,in_progress,ready_for_delivery,completed,cancelled',
        ]);

        // Transition guard (provider role)
        if (!$order->canTransitionTo($validated['status'], 'provider')) {
            $msg = 'Invalid status transition from ' . $order->status . ' to ' . $validated['status'];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => $msg], 422);
            }
            return back()->withErrors(['status' => $msg]);
        }

        $oldStatus = $order->status;
        $order->status = $validated['status'];

        // Production timestamps
        if ($validated['status'] === 'in_progress' && !$order->production_started_at) {
            $order->production_started_at = now();
        }
        if ($validated['status'] === 'ready_for_delivery' && !$order->ready_at) {
            $order->ready_at = now();
        }
        if ($validated['status'] === 'completed' && !$order->completed_at) {
            $order->completed_at = now();
        }
        if ($validated['status'] === 'cancelled' && !$order->cancelled_at) {
            $order->cancelled_at = now();
        }

        $order->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'status' => $order->status]);
        }

        return redirect()->back()->with('success', __('messages.updated_success'));
    }
        public function sendQuote(Request $request, CustomOrder $order)
    {
        $provider = Provider::where('user_id', Auth::id())->first();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        if (!in_array($order->status, ['pending', 'under_review', 'revision_requested'], true)) {
            return back()->withErrors(['status' => 'Cannot send quote — order is "' . $order->status . '".']);
        }

        $validated = $request->validate([
            'quoted_amount'    => 'required|numeric|min:1',
            'currency'         => 'nullable|string|size:3',
            'materials'        => 'nullable|string|max:500',
            'dimensions'       => 'nullable|string|max:100',
            'quantity'         => 'required|integer|min:1|max:999',
            'production_days'  => 'required|integer|min:1|max:365',
            'delivery_terms'   => 'nullable|string|max:1000',
            'advance_proposal' => 'nullable|numeric|min:0',
            'valid_until'      => 'required|date|after:today',
            'provider_message' => 'nullable|string|max:2000',
        ]);

        $quote = DB::transaction(function () use ($order, $validated) {
            // Supersede previous pending quotes
            CustomOrderQuote::where('custom_order_id', $order->id)
                ->where('status', 'pending')
                ->update(['status' => 'superseded']);

            $quote = CustomOrderQuote::create([
                'custom_order_id'  => $order->id,
                'version'          => $order->nextQuoteVersion(),
                'quoted_amount'    => $validated['quoted_amount'],
                'currency'         => $validated['currency'] ?? 'NPR',
                'materials'        => $validated['materials'] ?? null,
                'dimensions'       => $validated['dimensions'] ?? null,
                'quantity'         => $validated['quantity'],
                'production_days'  => $validated['production_days'],
                'delivery_terms'   => $validated['delivery_terms'] ?? null,
                'advance_proposal' => $validated['advance_proposal'] ?? null,
                'valid_until'      => $validated['valid_until'],
                'provider_message' => $validated['provider_message'] ?? null,
                'status'           => 'pending',
            ]);

            // Quote creation itself IS the transition — bypass state machine
            $order->status = 'quoted';
            $order->quoted_amount = $validated['quoted_amount'];
            $order->quoted_at = now();
            $order->save();

            return $quote;
        });

        try {
            \Mail::to($order->client_email)
                ->queue(new \App\Mail\ArtCraftQuoteSentMail($order, $quote, $provider));
        } catch (\Throwable $e) {
            \Log::warning('ArtCraft quote email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Quote #' . $quote->version . ' sent to ' . $order->client_email);
    }

    public function storeArtwork(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        // Booking quota check (monthly limit)
        try {
            app(\App\Services\BookingLimitService::class)->reserve($provider);
        } catch (\DomainException $e) {
            return back()->withErrors(['limit' => __('messages.booking_limit_reached')])->withInput();
        }

        if ($redirect = $this->checkPlanLimit($provider, 'max_artworks', \App\Models\Artwork::class, 'artworks')) {
            return $redirect;
        }

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
            'gradient' => 'linear-gradient(135deg,#f9dad0,#c65d3b)',
            'status' => 'available',
        ]);

        return back()->with('success', __('messages.artwork_created_success'));
    }

    public function destroyArtwork(Artwork $artwork)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $artwork->provider_id === $provider->id, 403);

        $artwork->delete();
        return back()->with('success', __('messages.artwork_deleted_success'));
    }

    public function storeCustomOrder(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
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
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $order->provider_id === $provider->id, 403);

        $order->delete();
        return back()->with('success', __('messages.order_deleted_success'));
    }

    public function galleryPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $artworks = Artwork::where('provider_id', $provider->id)->latest()->get();

        // 🆕 Sidebar artist card values (layout ले चाहिन्छ)
        $completedWorks = Artwork::where('provider_id', $provider->id)->count();
        $rating = 4.9;
        $clients = CustomOrder::where('provider_id', $provider->id)
            ->distinct('client_email')->count('client_email');

        return view('provider.art-craft.gallery', compact(
            'provider', 'artworks', 'completedWorks', 'rating', 'clients'
        ));
    }

    public function ordersPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $orders = CustomOrder::where('provider_id', $provider->id)->latest()->get();

        // 🆕 Sidebar artist card values (layout ले चाहिन्छ)
        $completedWorks = Artwork::where('provider_id', $provider->id)->count();
        $rating = 4.9;
        $clients = CustomOrder::where('provider_id', $provider->id)
            ->distinct('client_email')->count('client_email');

        return view('provider.art-craft.orders', compact(
            'provider', 'orders', 'completedWorks', 'rating', 'clients'
        ));
    }

    public function showOrder(CustomOrder $order)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);
        abort_unless((int)$order->provider_id === (int)$provider->id, 403);
        $order->load('provider');
        $provider_layout = 'layouts.provider-art-craft';
        return view('provider.art-craft.orders.show', compact('order', 'provider', 'provider_layout'));
    }
        public function artistsPage()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->where('slug', 'art-craft'))
            ->first();
        abort_unless($provider, 403);

        $artists = Artwork::where('provider_id', $provider->id)
            ->whereNotNull('artist_name')
            ->where('artist_name', '!=', '')
            ->selectRaw('artist_name, COUNT(*) as artwork_count, SUM(CASE WHEN status = ? THEN price ELSE 0 END) as revenue, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sold_count', ['sold', 'sold'])
            ->groupBy('artist_name')
            ->orderByDesc('artwork_count')
            ->get();

        $totalArtists = $artists->count();

        // Sidebar stats (match dashboard pattern)
        $totalArtworks = Artwork::where('provider_id', $provider->id)->count();
        $artistName = $provider->name;
        $rating = 4.9;
        $clients = CustomOrder::where('provider_id', $provider->id)
            ->distinct('client_email')->count('client_email');
                    // Sidebar compatibility (layout uses $completedWorks)
        $completedWorks = $totalArtworks;

                return view('provider.art-craft.artists', compact(
            'provider', 'artists', 'totalArtists',
            'totalArtworks', 'completedWorks', 'artistName', 'rating', 'clients'
        ));
    }

    public function aiProductDescription(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);
        return response()->json($this->artcraftAi->productDescription($provider));
    }

    public function aiPricingAssistant(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);
        return response()->json($this->artcraftAi->pricingAssistant($provider));
    }
    public function salesPage()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->whereHas('types', fn($q) => $q->where('slug', 'art-craft'))
            ->first();
        abort_unless($provider, 403);

        // Sold artworks (all-time)
        $soldArtworks = Artwork::where('provider_id', $provider->id)
            ->where('status', 'sold')
            ->orderByDesc('updated_at')
            ->get();

        $totalRevenue = (float) $soldArtworks->sum('price');
        $soldCount = $soldArtworks->count();
        $avgPrice = $soldCount > 0 ? $totalRevenue / $soldCount : 0;

        // 7-day chart (uses updated_at — no sold_at column exists)
        $days = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->format('Y-m-d'));
        $salesRaw = Artwork::where('provider_id', $provider->id)
            ->where('status', 'sold')
            ->where('updated_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(updated_at) as d, SUM(price) as v')
            ->groupBy('d')->pluck('v', 'd');

        $chartData = [];
        foreach ($days as $date) {
            $chartData[] = [
                'day' => \Carbon\Carbon::parse($date)->format('D'),
                'value' => (float) ($salesRaw[$date] ?? 0),
            ];
        }
        $chartTotal = collect($chartData)->sum('value');

        // Sidebar stats
        $totalArtworks = Artwork::where('provider_id', $provider->id)->count();
        $artistName = $provider->name;
        $rating = 4.9;
        $clients = CustomOrder::where('provider_id', $provider->id)
            ->distinct('client_email')->count('client_email');
                    // Sidebar compatibility (layout uses $completedWorks)
        $completedWorks = $totalArtworks;

                return view('provider.art-craft.sales', compact(
            'provider', 'soldArtworks', 'totalRevenue', 'soldCount', 'avgPrice',
            'chartData', 'chartTotal',
            'totalArtworks', 'completedWorks', 'artistName', 'rating', 'clients'
        ));
    }

    public function updateArtwork(Request $request, Artwork $artwork)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $artwork->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'artist_name' => 'nullable|string|max:100',
            'category' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:available,sold,custom',
            'emoji' => 'nullable|string|max:10',
        ]);

        $artwork->update($validated);

        return redirect()->back()->with('success', __('messages.artwork_updated_success'));
    }
}
