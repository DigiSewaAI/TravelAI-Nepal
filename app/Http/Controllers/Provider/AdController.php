<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdPayment;
use App\Services\AdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function __construct(private AdService $adService) {}

    private function getProvider()
    {
        $user = Auth::user();
        $provider = $user->provider ?? \App\Models\Provider::where('user_id', $user->id)->first();
        if (!$provider) abort(403, 'Provider profile not found.');
        return $provider;
    }

    /**
     * My Ads — list of all ads this provider has submitted.
     */
    public function index()
    {
        $provider = $this->getProvider();

        $ads = Ad::where('provider_id', $provider->id)
            ->with('payments')
            ->latest()
            ->paginate(15);

        $stats = [
            'active'   => Ad::where('provider_id', $provider->id)->where('status', 'active')->count(),
            'pending'  => Ad::where('provider_id', $provider->id)->whereIn('status', ['payment_pending', 'pending_review'])->count(),
            'rejected' => Ad::where('provider_id', $provider->id)->where('status', 'rejected')->count(),
            'expired'  => Ad::where('provider_id', $provider->id)->where('status', 'expired')->count(),
        ];

        return view('provider.ads.index', compact('ads', 'stats'));
    }

    /**
     * Buy Ad form.
     */
    public function create()
    {
        $pricing = Ad::PRICING;
        return view('provider.ads.create', compact('pricing'));
    }

    /**
     * Store new ad + payment proof.
     */
    public function store(Request $request)
    {
        $provider = $this->getProvider();

        $validated = $request->validate([
            'title'              => 'required|string|max:150',
            'description'        => 'nullable|string|max:500',
            'image'              => 'required|image|max:3072', // 3MB
            'link_url'           => 'required|string|max:500',
            'link_target'        => 'in:_blank,_self',
            'cta_text'           => 'nullable|string|max:50',
            'duration_days'      => 'required|integer|in:7,15,30',
            'target_destination' => 'nullable|string|max:100',
            'target_category_id' => 'nullable|integer|exists:service_categories,id',
            'payment_method'     => 'required|string|max:50',
            'payment_reference'  => 'nullable|string|max:100',
            'payment_proof'      => 'required|image|max:3072',
        ]);

        $price = Ad::calculatePrice((int) $validated['duration_days']);
        if ($price === 0) {
            return back()->withErrors(['duration_days' => 'Invalid duration.'])->withInput();
        }

        $imagePath = $request->file('image')->store('ads/images', 'public');
        $proofPath = $request->file('payment_proof')->store('ads/proofs', 'public');

        $ad = Ad::create([
            'provider_id'         => $provider->id,
            'title'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'image_path'          => $imagePath,
            'link_url'            => $validated['link_url'],
            'link_target'         => $validated['link_target'] ?? '_blank',
            'cta_text'            => $validated['cta_text'] ?? 'Learn More',
            'duration_days'       => $validated['duration_days'],
            'price_paid'          => $price,
            'payment_status'      => 'pending',
            'payment_proof_path'  => $proofPath,
            'payment_submitted_at' => now(),
            'status'              => 'pending_review',
            'target_destination'  => $validated['target_destination'] ?? null,
            'target_category_id'  => $validated['target_category_id'] ?? null,
            'placement'           => 'dashboard_featured',
        ]);

        AdPayment::create([
            'ad_id'             => $ad->id,
            'provider_id'       => $provider->id,
            'amount'            => $price,
            'currency'          => 'NPR',
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'proof_path'        => $proofPath,
            'status'            => 'pending',
        ]);

        return redirect()
            ->route('provider.ads.show', $ad)
            ->with('success', __('messages.ads_created_success'));
    }

    /**
     * Ad detail + stats.
     */
    public function show(Ad $ad)
    {
        $provider = $this->getProvider();
        if ($ad->provider_id !== $provider->id) abort(403);

        $ad->load(['payments', 'verifiedBy']);

        return view('provider.ads.show', compact('ad'));
    }
}