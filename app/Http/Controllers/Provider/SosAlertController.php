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

        return view('provider.sos-alerts.index', compact('alerts', 'activeCount', 'status'));
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

        return view('provider.sos-alerts.show', compact('sos'));
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