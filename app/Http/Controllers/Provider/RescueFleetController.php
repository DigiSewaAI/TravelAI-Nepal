<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\RescueFleet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RescueFleetController extends Controller
{
    private function getProvider()
    {
        $user = Auth::user();
        $provider = $user?->provider ?? \App\Models\Provider::where('user_id', $user?->id)->first();
        if (!$provider) abort(403, 'Provider profile not found.');
        return $provider;
    }

    public function index()
    {
        $provider = $this->getProvider();
        $fleets = RescueFleet::where('provider_id', $provider->id)->latest()->get();

        // Stats for cards
        $stats = [
            'total'       => $fleets->count(),
            'available'   => $fleets->where('status', 'available')->count(),
            'on_mission'  => $fleets->where('status', 'on_mission')->count(),
            'maintenance' => $fleets->where('status', 'maintenance')->count(),
        ];

        $rescueProvider = \App\Models\RescueProvider::where('provider_id', $provider->id)->first();

        return view('provider.rescue.fleet', compact('provider', 'rescueProvider', 'fleets', 'stats'));
    }

    public function store(Request $request)
    {
        $provider = $this->getProvider();

        $validated = $request->validate([
            'registration_number' => 'required|string|max:20',
            'type'                => 'required|in:helicopter,ground_ambulance,medical_team',
            'model'               => 'nullable|string|max:100',
            'max_altitude_ft'     => 'nullable|integer|min:0|max:30000',
            'pilot_name'          => 'nullable|string|max:100',
            'status'              => 'required|in:available,on_mission,maintenance,offline',
        ]);

        $validated['provider_id'] = $provider->id;

        RescueFleet::create($validated);

        return redirect()->route('provider.rescue.fleet')
            ->with('success', __('messages.fleet_vehicle_added') ?? 'Vehicle added successfully.');
    }

    public function update(Request $request, RescueFleet $fleet)
    {
        $provider = $this->getProvider();
        if ($fleet->provider_id !== $provider->id) abort(403);

        $validated = $request->validate([
            'registration_number' => 'required|string|max:20',
            'type'                => 'required|in:helicopter,ground_ambulance,medical_team',
            'model'               => 'nullable|string|max:100',
            'max_altitude_ft'     => 'nullable|integer|min:0|max:30000',
            'pilot_name'          => 'nullable|string|max:100',
            'status'              => 'required|in:available,on_mission,maintenance,offline',
        ]);

        $fleet->update($validated);

        return redirect()->route('provider.rescue.fleet')
            ->with('success', __('messages.fleet_vehicle_updated') ?? 'Vehicle updated successfully.');
    }

    public function destroy(RescueFleet $fleet)
    {
        $provider = $this->getProvider();
        if ($fleet->provider_id !== $provider->id) abort(403);

        $fleet->delete();

        return redirect()->route('provider.rescue.fleet')
            ->with('success', __('messages.fleet_vehicle_deleted') ?? 'Vehicle deleted successfully.');
    }
}