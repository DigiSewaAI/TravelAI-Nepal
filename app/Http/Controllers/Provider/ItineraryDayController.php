<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceItineraryDay;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ItineraryDayController extends Controller
{
    use AuthorizesRequests;

    /**
     * List itinerary days for a service (main editor page).
     */
    public function index(Service $service)
    {
        $this->authorize('update', $service);

        $service->load([
            'itineraryDays.items',
            'itineraryDays.media',
        ]);

        return view('provider.services.itinerary.index', compact('service'));
    }

    /**
     * Store a new itinerary day.
     * Server assigns day_number = MAX+1 (C1 binding).
     * Client-supplied day_number is IGNORED.
     */
    public function store(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'start_waypoint_id'      => 'nullable|integer|exists:waypoints,id',
            'end_waypoint_id'        => 'nullable|integer|exists:waypoints,id',
            'overnight_waypoint_id'  => 'nullable|integer|exists:waypoints,id',
            'distance_km'            => 'nullable|numeric|min:0',
            'estimated_time_hours'   => 'nullable|numeric|min:0',
            'elevation_gain_m'       => 'nullable|integer|min:0',
            'elevation_loss_m'       => 'nullable|integer|min:0',
            'altitude_m'             => 'nullable|integer',
            'meals_included'         => 'nullable|array',
            'meals_included.*'       => 'in:B,L,D',
            'accommodation'          => 'nullable|string|max:255',
        ]);

                // Server-authoritative day_number (C1 binding)
        // Wrapped in transaction + row lock to prevent race condition
        DB::transaction(function () use ($service, $validated) {
            // Lock the service row to serialize concurrent day creation
            $lockedService = Service::where('id', $service->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedService) {
                abort(404);
            }

            $nextNumber = (int) DB::table('service_itinerary_days')
                ->where('service_id', $lockedService->id)
                ->max('day_number') + 1;

            $validated['service_id'] = $lockedService->id;
            $validated['day_number'] = $nextNumber;

            ServiceItineraryDay::create($validated);
        });

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Itinerary day added.');
    }

    /**
     * Update an existing itinerary day.
     * day_number is NOT editable via this endpoint.
     */
    public function update(Request $request, Service $service, ServiceItineraryDay $day)
    {
        $this->authorize('update', $service);

        // SL8 defense in depth
        if ($day->service_id !== $service->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'start_waypoint_id'      => 'nullable|integer|exists:waypoints,id',
            'end_waypoint_id'        => 'nullable|integer|exists:waypoints,id',
            'overnight_waypoint_id'  => 'nullable|integer|exists:waypoints,id',
            'distance_km'            => 'nullable|numeric|min:0',
            'estimated_time_hours'   => 'nullable|numeric|min:0',
            'elevation_gain_m'       => 'nullable|integer|min:0',
            'elevation_loss_m'       => 'nullable|integer|min:0',
            'altitude_m'             => 'nullable|integer',
            'meals_included'         => 'nullable|array',
            'meals_included.*'       => 'in:B,L,D',
            'accommodation'          => 'nullable|string|max:255',
        ]);

        $day->update($validated);

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Day updated.');
    }

    /**
     * Delete an itinerary day.
     * Remaining days are renumbered 1..N (C1 binding, two-phase).
     */
    public function destroy(Service $service, ServiceItineraryDay $day)
    {
        $this->authorize('update', $service);

        if ($day->service_id !== $service->id) {
            abort(404);
        }

        DB::transaction(function () use ($service, $day) {
            $day->delete();

            // Renumber remaining days (C1 binding)
            $remainingIds = DB::table('service_itinerary_days')
                ->where('service_id', $service->id)
                ->orderBy('day_number')
                ->pluck('id')
                ->toArray();

            if (empty($remainingIds)) {
                return;
            }

            // Two-phase to avoid UNIQUE(service_id, day_number) violation
            foreach ($remainingIds as $id) {
                DB::table('service_itinerary_days')
                    ->where('id', $id)
                    ->update(['day_number' => -$id]);
            }

            foreach ($remainingIds as $index => $id) {
                DB::table('service_itinerary_days')
                    ->where('id', $id)
                    ->update(['day_number' => $index + 1]);
            }
        });

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Day removed.');
    }

    /**
     * Reorder all days for a service.
     * Receives full ordered array of day IDs (SL3 binding, bulk JSON).
     * Two-phase update required for UNIQUE(service_id, day_number).
     */
    public function reorder(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'order'   => 'required|array|min:1',
            'order.*' => 'required|integer|exists:service_itinerary_days,id',
        ]);

        $orderedIds = $validated['order'];

        // SL8: every ID must belong to this service
        $matchingCount = DB::table('service_itinerary_days')
            ->where('service_id', $service->id)
            ->whereIn('id', $orderedIds)
            ->count();

        if ($matchingCount !== count($orderedIds)) {
            return back()->withErrors([
                'order' => 'Invalid day IDs for this service.',
            ]);
        }

        DB::transaction(function () use ($orderedIds) {
            // Phase 1: temporary negative
            foreach ($orderedIds as $id) {
                DB::table('service_itinerary_days')
                    ->where('id', $id)
                    ->update(['day_number' => -$id]);
            }

            // Phase 2: assign 1..N
            foreach ($orderedIds as $index => $id) {
                DB::table('service_itinerary_days')
                    ->where('id', $id)
                    ->update(['day_number' => $index + 1]);
            }
        });

        return back()->with('success', 'Days reordered.');
    }
}