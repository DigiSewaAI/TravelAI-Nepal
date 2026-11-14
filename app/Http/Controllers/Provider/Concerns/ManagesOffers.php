<?php

namespace App\Http\Controllers\Provider\Concerns;

use App\Models\ProviderOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ManagesOffers
{
    public function offersPage()
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403, 'Provider not found.');
        
        $offers = ProviderOffer::where('provider_id', $provider->id)
            ->orderByDesc('is_active')
            ->orderBy('valid_until')
            ->get();

        // रुटको नामबाट भर्टिकल पत्ता लगाउने (जस्तै: provider.restaurant.offers.list -> restaurant)
        $routeName = request()->route()->getName();
        $parts = explode('.', $routeName);
        $verticalPrefix = $parts[1] ?? 'restaurant';

        return view("provider.{$verticalPrefix}.offers", compact('provider', 'offers', 'verticalPrefix'));
    }

    public function storeOffer(Request $request)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'discount' => 'required|string|max:50',
            'valid_until' => 'required|date|after_or_equal:today',
            'is_active' => 'boolean'
        ]);

        ProviderOffer::create([
            'provider_id' => $provider->id,
            'title' => $validated['title'],
            'discount' => $validated['discount'],
            'valid_until' => $validated['valid_until'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Offer created successfully.');
    }

    public function updateOffer(Request $request, ProviderOffer $offer)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $offer->provider_id === $provider->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'discount' => 'required|string|max:50',
            'valid_until' => 'required|date',
            'is_active' => 'boolean'
        ]);

        $offer->update([
            'title' => $validated['title'],
            'discount' => $validated['discount'],
            'valid_until' => $validated['valid_until'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Offer updated successfully.');
    }

    public function deleteOffer(ProviderOffer $offer)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider && $offer->provider_id === $provider->id, 403);
        $offer->delete();
        return back()->with('success', 'Offer deleted successfully.');
    }
}