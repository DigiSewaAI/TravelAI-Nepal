@extends('layouts.provider')

@section('title', 'Edit Itinerary — ' . $service->name)
@section('header', 'Itinerary: ' . $service->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Back + Add Day --}}
    <div class="flex justify-between items-center flex-wrap gap-3">
        <a href="{{ route('provider.services.edit', $service) }}"
           class="text-sm text-gray-600 hover:text-gray-900">
            ← Back to service
        </a>
        <div class="flex items-center gap-2">
            {{-- Lifecycle status badge --}}
            @if($service->isItineraryPublished())
                <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-green-100 text-green-700">
                    ● Published
                </span>
            @else
                <span class="text-xs font-bold px-3 py-1.5 rounded-full bg-gray-100 text-gray-600">
                    ○ Draft
                </span>
            @endif

            {{-- Preview (always visible) --}}
            <a href="{{ route('provider.services.itinerary.preview', $service) }}"
               target="_blank"
               class="text-sm font-semibold px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                Preview
            </a>

            {{-- Publish / Unpublish --}}
            @if($service->isItineraryPublished())
                <form method="POST"
                      action="{{ route('provider.services.itinerary.unpublish', $service) }}"
                      class="inline"
                      onsubmit="return confirm('Unpublish itinerary? It will be hidden from public until re-published.');">
                    @csrf
                    <button type="submit"
                            class="text-sm font-semibold px-3 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition">
                        Unpublish
                    </button>
                </form>
            @else
                <form method="POST"
                      action="{{ route('provider.services.itinerary.publish', $service) }}"
                      class="inline"
                      onsubmit="return confirm('Publish itinerary? It will become visible publicly.');">
                    @csrf
                    <button type="submit"
                            class="text-sm font-semibold px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white transition">
                        Publish
                    </button>
                </form>
            @endif

            {{-- Add Day (existing) --}}
            <button type="button" onclick="document.getElementById('add-day-panel').classList.toggle('hidden')"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                + Add Day
            </button>
        </div>
    </div>

    {{-- Add Day Panel (hidden by default) --}}
    <div id="add-day-panel" class="hidden bg-white rounded-xl shadow-sm border p-5">
        <h3 class="font-semibold text-gray-800 mb-3">New Day</h3>
        <form method="POST" action="{{ route('provider.services.itinerary.days.store', $service) }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" required maxlength="255"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Create Day
                    </button>
                    <button type="button" onclick="document.getElementById('add-day-panel').classList.add('hidden')"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Days list --}}
    @if($service->itineraryDays->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-gray-500">
            No itinerary days yet. Click <strong>Add Day</strong> to start.
        </div>
    @else
        <div id="days-container">
            @foreach($service->itineraryDays as $day)
                @include('provider.services.itinerary._day_card', ['day' => $day, 'service' => $service])
            @endforeach
        </div>
    @endif

</div>

{{-- Hidden form: day reorder --}}
<form id="day-reorder-form" method="POST" action="{{ route('provider.services.itinerary.days.reorder', $service) }}" class="hidden">
    @csrf
    <input type="hidden" name="order" id="day-reorder-input">
</form>

{{-- Hidden form: item reorder --}}
<form id="item-reorder-form" method="POST" action="{{ route('provider.services.itinerary.items.reorder', $service) }}" class="hidden">
    @csrf
    <input type="hidden" name="day_id" id="item-reorder-day-id">
    <input type="hidden" name="order" id="item-reorder-input">
</form>

<script>
    function moveDay(id, direction) {
        var cards = document.querySelectorAll('#days-container [data-day-id]');
        var ids = [];
        cards.forEach(function (c) { ids.push(parseInt(c.dataset.dayId, 10)); });
        var idx = ids.indexOf(id);
        if (direction === 'up' && idx > 0) {
            var t = ids[idx - 1]; ids[idx - 1] = ids[idx]; ids[idx] = t;
        } else if (direction === 'down' && idx < ids.length - 1) {
            var t = ids[idx + 1]; ids[idx + 1] = ids[idx]; ids[idx] = t;
        } else { return; }
        document.getElementById('day-reorder-input').value = JSON.stringify(ids);
        document.getElementById('day-reorder-form').submit();
    }

    function moveItem(dayId, itemId, direction) {
        var rows = document.querySelectorAll('[data-item-id][data-parent-day="' + dayId + '"]');
        var ids = [];
        rows.forEach(function (r) { ids.push(parseInt(r.dataset.itemId, 10)); });
        var idx = ids.indexOf(itemId);
        if (direction === 'up' && idx > 0) {
            var t = ids[idx - 1]; ids[idx - 1] = ids[idx]; ids[idx] = t;
        } else if (direction === 'down' && idx < ids.length - 1) {
            var t = ids[idx + 1]; ids[idx + 1] = ids[idx]; ids[idx] = t;
        } else { return; }
        document.getElementById('item-reorder-day-id').value = dayId;
        document.getElementById('item-reorder-input').value = JSON.stringify(ids);
        document.getElementById('item-reorder-form').submit();
    }
</script>
@endsection