<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\SosAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SosAlertController extends Controller
{
    /**
     * Resolve vertical-specific layout based on provider type.
     */
    private function getProviderLayout($provider): string
    {
        $map = [
            'restaurant'         => 'layouts.provider-restaurant',
            'hotel'              => 'layouts.provider-hotel',
            'resort'             => 'layouts.provider-hotel',
            'lodge'              => 'layouts.provider-hotel',
            'homestay'           => 'layouts.provider-hotel',
            'transport-provider' => 'layouts.provider-transport',
            'shop-owner'         => 'layouts.provider-inventory',
            'rental-provider'    => 'layouts.provider-inventory',
            'wholesale-provider' => 'layouts.provider-inventory',
            'art-craft'          => 'layouts.provider-art-craft',
            'guide'              => 'layouts.provider-individual',
            'porter'             => 'layouts.provider-individual',
            'photographer'       => 'layouts.provider-individual',
            'money-changer'      => 'layouts.provider-money-changer',
        ];
        return $map[$provider?->types->first()?->slug] ?? 'layouts.provider';
    }
    /**
     * Resolve current provider (with super_admin fallback).
     */
    private function getProvider()
    {
        $user = Auth::user();
        $provider = $user->provider ?? \App\Models\Provider::where('user_id', $user->id)->first();

        if (!$provider) {
            abort(403, 'Provider profile not found.');
        }

        return $provider;
    }

    /**
     * List all SOS alerts for this provider.
     */
    public function index(Request $request)
    {
        $provider = $this->getProvider();
        $status = $request->query('status', 'all');

        $query = SosAlert::where('provider_id', $provider->id)
            ->with(['traveler', 'booking.service']);

        if ($status === 'active') {
            $query->whereIn('status', ['pending', 'sent']);
        } elseif ($status === 'resolved') {
            $query->where('status', 'resolved');
        }

        $alerts = $query->latest()->paginate(15);

        $activeCount = SosAlert::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'sent'])
            ->count();

        $provider_layout = $this->getProviderLayout($provider);

        $stats = [
            'total' => \App\Models\SosAlert::where('provider_id', $provider->id)->count(),
            'active' => \App\Models\SosAlert::where('provider_id', $provider->id)->whereIn('status', ['pending','sent'])->count(),
            'resolved' => \App\Models\SosAlert::where('provider_id', $provider->id)->where('status', 'resolved')->count(),
        ];

        return view(($provider_layout === 'layouts.provider') ? 'provider.sos-alerts.index' : 'provider.sos-alerts.index-premium', compact('alerts', 'activeCount', 'status', 'provider_layout', 'stats'));
    }

    /**
     * Show SOS alert detail.
     */
    public function show(SosAlert $sos)
    {
        $provider = $this->getProvider();

        if ($sos->provider_id !== $provider->id) {
            abort(403);
        }

        $sos->load(['traveler', 'booking.service']);

        $provider_layout = $this->getProviderLayout($provider);
return view('provider.sos-alerts.show', compact('sos', 'provider_layout'));
    }

    /**
     * Mark SOS as resolved.
     */
    public function resolve(SosAlert $sos)
    {
        $provider = $this->getProvider();

        if ($sos->provider_id !== $provider->id) {
            abort(403);
        }

        $sos->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);

        // Clear sidebar badge cache
        Cache::forget('provider_sos_unread_' . $provider->id);

        return back()->with('success', __('messages.sos_alerts_resolved_msg'));
    }
}