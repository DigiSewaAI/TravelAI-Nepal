<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\SosAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SosAlertController extends Controller
{
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
            'rescue-service'     => 'layouts.provider-rescue',
        ];
        return $map[$provider?->types->first()?->slug] ?? 'layouts.provider';
    }

    private function getProvider()
    {
        $user = Auth::user();
        $provider = $user->provider ?? \App\Models\Provider::where('user_id', $user->id)->first();
        if (!$provider) abort(403, 'Provider profile not found.');
        return $provider;
    }

    private function authorizeAlert(SosAlert $sos): void
    {
        $provider = $this->getProvider();
        if ($sos->provider_id !== $provider->id) abort(403);
    }

    public function index(Request $request)
    {
        $provider = $this->getProvider();
        $status = $request->query('status', 'all');

        $query = SosAlert::where('provider_id', $provider->id)
            ->with(['traveler', 'booking.service']);

        if ($status === 'active') {
            $query->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding']);
        } elseif ($status === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($status === 'false_alarm') {
            $query->where('status', 'false_alarm');
        } elseif ($status === 'pending') {
            $query->whereIn('status', ['pending', 'sent']);
        }

        $alerts = $query->latest()->paginate(15);

        $activeCount = SosAlert::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])
            ->count();

        $provider_layout = $this->getProviderLayout($provider);

        $base = SosAlert::where('provider_id', $provider->id);
        $stats = [
            'total'        => (clone $base)->count(),
            'active'       => (clone $base)->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])->count(),
            'pending'      => (clone $base)->whereIn('status', ['pending', 'sent'])->count(),
            'acknowledged' => (clone $base)->where('status', 'acknowledged')->count(),
            'responding'   => (clone $base)->where('status', 'responding')->count(),
            'resolved'     => (clone $base)->where('status', 'resolved')->count(),
            'false_alarm'  => (clone $base)->where('status', 'false_alarm')->count(),
        ];

        return view(
            ($provider_layout === 'layouts.provider')
                ? 'provider.sos-alerts.index'
                : 'provider.sos-alerts.index-premium',
            compact('alerts', 'activeCount', 'status', 'provider_layout', 'stats')
        );
    }

    public function show(SosAlert $sos)
    {
        $this->authorizeAlert($sos);
        $sos->load(['traveler', 'booking.service', 'actions.user']);
        $provider = $this->getProvider();
        $provider_layout = $this->getProviderLayout($provider);
        return view(
            ($provider_layout === 'layouts.provider')
                ? 'provider.sos-alerts.show'
                : 'provider.sos-alerts.show-premium',
            compact('sos', 'provider_layout')
        );
    }

    public function acknowledge(SosAlert $sos)
    {
        $this->authorizeAlert($sos);
        if (!$sos->acknowledge(Auth::id())) {
            return back()->with('error', 'Cannot acknowledge at current state.');
        }
        Cache::forget('provider_sos_unread_' . $sos->provider_id);
        return back()->with('success', 'SOS acknowledged. You are responding.');
    }

    public function respond(SosAlert $sos)
    {
        $this->authorizeAlert($sos);
        if (!$sos->respond(Auth::id())) {
            return back()->with('error', 'Cannot mark responding at current state.');
        }
        return back()->with('success', 'SOS marked as responding — rescue is on the way.');
    }

    public function resolve(Request $request, SosAlert $sos)
    {
        $this->authorizeAlert($sos);
        $notes = $request->input('notes');
        if (!$sos->markResolved(Auth::id(), $notes)) {
            return back()->with('error', 'Cannot resolve at current state. Must be responding first.');
        }
        Cache::forget('provider_sos_unread_' . $sos->provider_id);
        return back()->with('success', __('messages.sos_alerts_resolved_msg'));
    }

    public function markFalseAlarm(Request $request, SosAlert $sos)
    {
        $this->authorizeAlert($sos);
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        if (!$sos->markFalseAlarm(Auth::id(), $validated['reason'])) {
            return back()->with('error', 'Cannot mark false alarm at current state.');
        }
        Cache::forget('provider_sos_unread_' . $sos->provider_id);
        return back()->with('success', 'SOS marked as false alarm.');
    }

    public function activeCountJson()
    {
        $provider = $this->getProvider();
        if (!$provider) return response()->json(['count' => 0, 'latest_id' => 0]);
        $q = SosAlert::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'sent']);
        return response()->json([
            'count'     => (int) $q->count(),
            'latest_id' => (int) ($q->max('id') ?? 0),
        ]);
    }
}