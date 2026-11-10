<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SosAlert;
use App\Models\RescueProvider;
use App\Models\RescueMission;
use App\Models\SosAlertAction;
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

        // 2. Escalation Queue
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

        // 4. Providers with real fleet count
        $providers = RescueProvider::with('provider')
            ->orderByDesc('is_available')
            ->take(5)
            ->get()
            ->map(function ($rp) {
                $rp->actual_fleet_count = \App\Models\RescueFleet::where('provider_id', $rp->provider_id)->count();
                return $rp;
            });

        // 5. Admin Actions Log
        $recentActions = SosAlertAction::whereIn('action', ['admin_notified', 'auto_escalated', 'dispatched'])
            ->latest()
            ->take(5)
            ->get();

                // 6. Pending docs count
        $pendingDocsCount = RescueProvider::all()->sum(function ($rp) {
            return collect($rp->documents ?? [])->where('status', 'pending')->count();
        });

        // 7. System Response (avg from completed missions)
        $avgResponseMinutes = RescueMission::whereNotNull('accepted_at')
            ->whereNotNull('created_at')
            ->get()
            ->avg(function ($m) {
                return $m->sos ? $m->sos->created_at->diffInMinutes($m->accepted_at) : 0;
            });

        $systemResponse = $avgResponseMinutes
            ? round($avgResponseMinutes, 1) . 'm'
            : '—';

        return view('admin.rescue.index', compact(
            'liveSosCount', 'slaBreaches', 'activeMissionsCount',
            'onlineProviders', 'totalProviders', 'escalations',
            'activeSos', 'providers', 'recentActions', 'pendingDocsCount',
            'systemResponse'
        ));
    }

    public function showSos(SosAlert $sos)
    {
        $sos->load(['traveler', 'provider', 'booking']);
        $rescueProviders = RescueProvider::with('provider')->where('is_available', true)->get();
        
        return view('admin.rescue.show', compact('sos', 'rescueProviders'));
    }
    public function missions()
    {
        // सबै Active Missions (Completed/Cancelled बाहेक)
        $missions = RescueMission::whereNotIn('status', ['completed', 'cancelled'])
            ->with(['sos.traveler', 'provider', 'fleet'])
            ->latest()
            ->get();

        return view('admin.rescue.missions', compact('missions'));
    }
        public function providers()
    {
        $providers = \App\Models\RescueProvider::with('provider')
            ->get();

        return view('admin.rescue.providers', compact('providers'));
    }
        public function history()
    {
        // Completed वा Cancelled भएका मिसनहरू मात्र
        $missions = RescueMission::whereIn('status', ['completed', 'cancelled'])
            ->with(['sos.traveler', 'provider', 'fleet'])
            ->latest()
            ->paginate(20);

        return view('admin.rescue.history', compact('missions'));
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