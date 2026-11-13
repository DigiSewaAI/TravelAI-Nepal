<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->provider;
        abort_unless($provider, 403, 'Provider profile not found.');

        $reviews = Review::where('provider_id', $provider->id)
            ->where('status', 'approved')
            ->with(['user', 'service'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $provider_layout = $this->resolveLayout($provider);

        return view('provider.reviews.index', compact('reviews', 'provider', 'provider_layout'));
    }

    /**
     * Resolve the correct layout based on provider's first type slug.
     * Mirrors existing system pattern (Hotel controller uses $provider_layout).
     */
    private function resolveLayout(?\App\Models\Provider $provider): string
    {
        $slug = $provider?->types->first()?->slug;

        return match (true) {
            in_array($slug, ['hotel', 'resort', 'lodge', 'homestay'], true)
                => 'layouts.provider-hotel',
            $slug === 'restaurant'
                => 'layouts.provider-restaurant',
            $slug === 'transport-provider'
                => 'layouts.provider-transport',
            $slug === 'art-craft'
                => 'layouts.provider-art-craft',
            in_array($slug, ['guide', 'porter', 'photographer', 'local-experience'], true)
                => 'layouts.provider-individual',
            in_array($slug, ['shop-owner', 'rental-provider', 'wholesale-provider'], true)
                => 'layouts.provider-inventory',
            $slug === 'money-changer'
                => 'layouts.provider-money-changer',
            default
                => 'layouts.provider', // trekking-agency, tour-agency, activity-provider, rescue (unused)
        };
    }

    public function show(Review $review)
    {
        $provider = Auth::user()->provider;
        abort_unless($provider, 403);
        abort_unless((int) $review->provider_id === (int) $provider->id, 403, 'Unauthorized');
        abort_unless($review->status === 'approved', 403, 'Review not approved.');

        $review->load(['user', 'service']);

        $provider_layout = $this->resolveLayout($provider);

        return view('provider.reviews.show', compact('review', 'provider', 'provider_layout'));
    }

    public function respond(Request $request, Review $review)
    {
        $provider = Auth::user()->provider;
        abort_unless($provider, 403);
        abort_unless((int) $review->provider_id === (int) $provider->id, 403, 'Unauthorized');
        abort_unless($review->status === 'approved', 403);
        abort_unless($review->provider_response === null, 403, 'Response already submitted.');

        $validated = $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        $review->provider_response = $validated['response'];
        $review->provider_responded_at = now();
        $review->save();

        if ($review->user) {
            $review->user->notify(new \App\Notifications\ReviewResponded($review));
        }

        return redirect()
            ->route('provider.reviews.show', $review)
            ->with('success', 'Response submitted successfully.');
    }
}