<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\RescueProvider;
use App\Models\SosAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RescueDashboardController extends Controller
{
    private function getProvider(): ?\App\Models\Provider
    {
        $user = Auth::user();
        return $user?->provider ?? \App\Models\Provider::where('user_id', $user?->id)->first();
    }

    private function getRescueProvider(): ?RescueProvider
    {
        $provider = $this->getProvider();
        if (!$provider) return null;
        return RescueProvider::where('provider_id', $provider->id)->first();
    }

    private function sharedData(): array
    {
        $provider = $this->getProvider();
        $rescueProvider = $this->getRescueProvider();

        $liveSos = SosAlert::where('provider_id', $provider?->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])
            ->count();

        // Fetch real fleet and mission count
        $activeMissions = \App\Models\RescueMission::where('provider_id', $provider?->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return [
            'provider'              => $provider,
            'rescueProvider'        => $rescueProvider,
            'liveSosCount'          => $liveSos,
            'activeMissionsCount'   => $activeMissions,
        ];
    }

    public function index()
    {
        $shared = $this->sharedData();
        $provider = $shared['provider'];

        $sosQueue = SosAlert::where('provider_id', $provider?->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged'])
            ->latest()
            ->take(10)
            ->get();

        return view('provider.rescue.dashboard', array_merge($shared, [
            'sosQueue' => $sosQueue,
        ]));
    }

    public function sosQueue()
    {
        $shared = $this->sharedData();
        $provider = $shared['provider'];

        $alerts = SosAlert::where('provider_id', $provider?->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])
            ->latest()
            ->paginate(15);

        return view('provider.rescue.sos', array_merge($shared, [
            'alerts' => $alerts,
        ]));
    }

    public function missions()
    {
        $shared = $this->sharedData();
        $provider = $shared['provider'];

        $missions = \App\Models\RescueMission::where('provider_id', $provider?->id)
            ->with(['sos.traveler', 'fleet'])
            ->latest()
            ->get();

        return view('provider.rescue.missions', array_merge($shared, [
            'missions' => $missions,
        ]));
    }

    public function gps()
    {
        return view('provider.rescue.gps', $this->sharedData());
    }

    public function audit()
    {
        return view('provider.rescue.audit', $this->sharedData());
    }

    public function fleet()
    {
        return view('provider.rescue.fleet', $this->sharedData());
    }

    public function staff()
    {
        return view('provider.rescue.staff', $this->sharedData());
    }

    public function coverage()
    {
        return view('provider.rescue.coverage', $this->sharedData());
    }

    public function updateCoverage(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $validated = $request->validate([
            'coverage_districts' => 'nullable|array',
            'coverage_districts.*' => 'string|max:50',
            'coverage_radius_km' => 'required|integer|min:10|max:500',
        ]);

        $rescueProvider->update([
            'coverage_districts' => $validated['coverage_districts'] ?? [],
            'coverage_radius_km' => $validated['coverage_radius_km'],
        ]);

        return back()->with('success', 'Coverage areas updated successfully.');
    }

    public function documents()
    {
        return view('provider.rescue.documents', $this->sharedData());
    }

    public function pricing()
    {
        return view('provider.rescue.pricing', $this->sharedData());
    }

    public function updatePricing(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $validated = $request->validate([
            'base_rate_npr'               => 'required|numeric|min:0',
            'per_km_rate_npr'             => 'required|numeric|min:0',
            'per_hour_rate_npr'           => 'required|numeric|min:0',
            'night_surcharge_pct'         => 'required|integer|min:0|max:100',
            'high_altitude_surcharge_pct' => 'required|integer|min:0|max:100',
        ]);

        $rescueProvider->update($validated);

        return back()->with('success', 'Pricing configuration updated successfully.');
    }

    public function insurance()
    {
        return view('provider.rescue.insurance', $this->sharedData());
    }

    public function revenue()
    {
        return view('provider.rescue.revenue', $this->sharedData());
    }

    public function reports()
    {
        return view('provider.rescue.reports', $this->sharedData());
    }

    public function security()
    {
        return view('provider.rescue.security', $this->sharedData());
    }

    public function toggleAvailability(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if ($rescueProvider) {
            $rescueProvider->update(['is_available' => !$rescueProvider->is_available]);
        }
        return back()->with('success', $rescueProvider?->is_available ? 'You are now On Duty' : 'You are now Off Duty');
    }
}