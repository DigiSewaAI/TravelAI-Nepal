<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceItineraryDay;
use App\Models\ServiceItineraryItem;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ItineraryItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a new item inside an itinerary day.
     * sort_order is server-assigned (MAX+1 per day).
     */
    public function store(Request $request, Service $service, ServiceItineraryDay $day)
    {
        $this->authorize('update', $service);

        // SL8 defense in depth
        if ($day->service_id !== $service->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_of_day' => 'nullable|in:morning,afternoon,evening',
            'is_optional' => 'nullable|boolean',
            'metadata'    => 'nullable|array',
        ]);

        // Server-assigned sort_order (per day)
        $nextOrder = (int) DB::table('service_itinerary_items')
            ->where('day_id', $day->id)
            ->max('sort_order') + 1;

        $validated['day_id']     = $day->id;
        $validated['sort_order'] = $nextOrder;
        $validated['is_optional'] = $validated['is_optional'] ?? false;

        ServiceItineraryItem::create($validated);

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Item added.');
    }

    /**
     * Update an existing item.
     * day_id and sort_order are NOT editable via this endpoint.
     */
    public function update(Request $request, Service $service, ServiceItineraryItem $item)
    {
        $this->authorize('update', $service);

        // SL8 defense in depth: item -> day -> service chain
        if (!$item->day || $item->day->service_id !== $service->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_of_day' => 'nullable|in:morning,afternoon,evening',
            'is_optional' => 'nullable|boolean',
            'metadata'    => 'nullable|array',
        ]);

        $validated['is_optional'] = $validated['is_optional'] ?? false;

        $item->update($validated);

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Item updated.');
    }

    /**
     * Delete an item.
     * Remaining items in the same day are renumbered 1..N (no UNIQUE constraint,
     * but keeps display order stable).
     */
    public function destroy(Service $service, ServiceItineraryItem $item)
    {
        $this->authorize('update', $service);

        // SL8 defense in depth
        if (!$item->day || $item->day->service_id !== $service->id) {
            abort(404);
        }

        $dayId = $item->day_id;

        DB::transaction(function () use ($item, $dayId) {
            $item->delete();

            // Renumber remaining items in this day
            $remainingIds = DB::table('service_itinerary_items')
                ->where('day_id', $dayId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            foreach ($remainingIds as $index => $id) {
                DB::table('service_itinerary_items')
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Item removed.');
    }

    /**
     * Reorder items inside a specific day.
     * Receives day_id + full ordered array of item IDs (SL3 binding, bulk JSON).
     * No two-phase needed — sort_order has NO UNIQUE constraint.
     */
    public function reorder(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'day_id'  => 'required|integer|exists:service_itinerary_days,id',
            'order'   => 'required|array|min:1',
            'order.*' => 'required|integer|exists:service_itinerary_items,id',
        ]);

        // SL8 defense in depth: day belongs to service
        $day = ServiceItineraryDay::find($validated['day_id']);
        if (!$day || $day->service_id !== $service->id) {
            abort(404);
        }

        // SL8: every item ID must belong to this day
        $matchingCount = DB::table('service_itinerary_items')
            ->where('day_id', $day->id)
            ->whereIn('id', $validated['order'])
            ->count();

        if ($matchingCount !== count($validated['order'])) {
            return back()->withErrors([
                'order' => 'Invalid item IDs for this day.',
            ]);
        }

        DB::transaction(function () use ($validated, $day) {
            foreach ($validated['order'] as $index => $itemId) {
                DB::table('service_itinerary_items')
                    ->where('id', $itemId)
                    ->where('day_id', $day->id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return back()->with('success', 'Items reordered.');
    }
}