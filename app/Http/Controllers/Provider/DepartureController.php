<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Departure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DepartureController extends Controller
{
    use AuthorizesRequests;

    /**
     * Departures are displayed inline on the itinerary page.
     * This route exists for naming consistency.
     */
    public function index(Service $service)
    {
        $this->authorize('update', $service);

        return redirect()
            ->route('provider.services.itinerary.index', $service);
    }

    public function store(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        // N2: requires published itinerary + at least 1 day
        if (!$service->isItineraryPublished() || $service->itineraryDays()->count() === 0) {
            return back()->withErrors([
                'departure' => 'Cannot create departure: itinerary must be published with at least one day.',
            ]);
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'capacity'   => 'required|integer|min:1',
        ]);

        // N6: exact same start_date blocked
        $duplicate = $service->departures()
            ->where('start_date', $validated['start_date'])
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'start_date' => 'A departure with this start date already exists for this service.',
            ])->withInput();
        }

        Departure::create([
            'service_id' => $service->id,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'capacity'   => $validated['capacity'],
            'status'     => 'scheduled',
        ]);

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Departure created.');
    }

    public function update(Request $request, Service $service, Departure $departure)
    {
        $this->authorize('update', $service);

        // SL8: defense in depth
        if ($departure->service_id !== $service->id) {
            abort(404);
        }

        // N7/N10/N11: only future scheduled editable
        if ($departure->status === 'cancelled') {
            return back()->withErrors([
                'departure' => 'Cancelled departures cannot be edited.',
            ]);
        }

        if ($departure->end_date->isPast()) {
            return back()->withErrors([
                'departure' => 'Past departures are read-only.',
            ]);
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'capacity'   => 'required|integer|min:1',
        ]);

        // N6: duplicate check excluding self
        $duplicate = $service->departures()
            ->where('start_date', $validated['start_date'])
            ->where('id', '!=', $departure->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors([
                'start_date' => 'Another departure already has this start date.',
            ])->withInput();
        }

        $departure->update([
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'capacity'   => $validated['capacity'],
        ]);

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Departure updated.');
    }

    public function cancel(Service $service, Departure $departure)
    {
        $this->authorize('update', $service);

        if ($departure->service_id !== $service->id) {
            abort(404);
        }

        // N8/N11: only scheduled → cancelled; no booking side effects
        if ($departure->status !== 'scheduled') {
            return back()->withErrors([
                'departure' => 'Only scheduled departures can be cancelled.',
            ]);
        }

        $departure->update(['status' => 'cancelled']);

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Departure cancelled.');
    }

    public function destroy(Service $service, Departure $departure)
    {
        $this->authorize('update', $service);

        if ($departure->service_id !== $service->id) {
            abort(404);
        }

        // N9: block hard delete when bookings exist
        if ($departure->bookings()->count() > 0) {
            return back()->withErrors([
                'departure' => 'Cannot delete a departure with bookings. Cancel it instead.',
            ]);
        }

        $departure->delete();

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Departure deleted.');
    }
}