<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SosAlert;
use App\Models\RescueProvider;
use App\Models\RescueMission;
use Illuminate\Http\Request;

class RescueCommandCenterController extends Controller
{
    public function index()
    {
        // 1. Stats
        $liveSosCount = SosAlert::whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])->count();
        $slaBreaches = SosAlert::whereIn('status', ['pending', 'sent'])
            ->where('created_at', '<=', now()->subMinutes(10))
            ->count();
        $activeMissionsCount = RescueMission::whereNotIn('status', ['completed', 'cancelled'])->count();
        
        $onlineProviders = RescueProvider::where('is_available', true)->count();
        $totalProviders = RescueProvider::count();

        // 2. Escalation Queue (SLA Breach > 10 mins)
        $escalations = SosAlert::whereIn('status', ['pending', 'sent'])
            ->where('created_at', '<=', now()->subMinutes(10))
            ->with('traveler')
            ->latest()
            ->get();

        // 3. All Active SOS
        $activeSos = SosAlert::whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])
            ->with(['traveler', 'provider'])
            ->latest()
            ->take(10)
            ->get();

        // 4. Providers Live Status
        $providers = RescueProvider::with('provider')
            ->orderByDesc('is_available')
            ->take(5)
            ->get();

        return view('admin.rescue.index', compact(
            'liveSosCount', 'slaBreaches', 'activeMissionsCount', 
            'onlineProviders', 'totalProviders', 'escalations', 
            'activeSos', 'providers'
        ));
    }

    public function showSos(SosAlert $sos)
    {
        $sos->load(['traveler', 'provider', 'booking']);
        $rescueProviders = RescueProvider::with('provider')->where('is_available', true)->get();
        
        return view('admin.rescue.show', compact('sos', 'rescueProviders'));
    }

    public function manualDispatch(Request $request, SosAlert $sos)
    {
        $validated = $request->validate([
            'provider_id' => 'required|exists:providers,id',
        ]);

        // Create a mission manually
        RescueMission::create([
            'mission_number' => 'RSC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'sos_alert_id'   => $sos->id,
            'provider_id'    => $validated['provider_id'],
            'status'         => 'dispatched',
            'pickup_lat'     => $sos->latitude,
            'pickup_lng'     => $sos->longitude,
        ]);

        // Update SOS status
        $sos->update(['status' => 'responding']);

        return redirect()->route('admin.rescue.index')
            ->with('success', 'SOS manually dispatched to provider successfully.');
    }
}