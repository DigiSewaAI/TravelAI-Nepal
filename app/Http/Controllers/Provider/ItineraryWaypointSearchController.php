<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Waypoint;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItineraryWaypointSearchController extends Controller
{
    use AuthorizesRequests;

    /**
     * PROVIDER-ITINERARY-08: Waypoint search for itinerary day picker.
     *
     * Server-side search only. Used by provider itinerary editor
     * to populate Start / Overnight / End waypoint selectors.
     *
     * - Auth: provider ownership via ServicePolicy
     * - Source: existing active waypoints only (no creation)
     * - Output: minimal JSON (id, name, lat, lng, altitude)
     */
    public function search(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Waypoint::query()
            ->whereNull('deleted_at')
            ->where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'latitude', 'longitude', 'altitude']);

        return response()->json([
            'results' => $results->map(fn ($w) => [
                'id'        => $w->id,
                'name'      => $w->name,
                'latitude'  => (float) $w->latitude,
                'longitude' => (float) $w->longitude,
                'altitude'  => $w->altitude,
            ])->values(),
        ]);
    }
}