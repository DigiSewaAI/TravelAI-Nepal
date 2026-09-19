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

    {{-- PROVIDER-ITINERARY-09B-02: Departures --}}
    <div class="bg-white rounded-xl shadow-sm border p-5 mt-6">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ __('messages.departures') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $service->departures->count() }} {{ __('messages.departures') }}
                </p>
            </div>

            @if($service->isItineraryPublished() && $service->itineraryDays->isNotEmpty())
                <button type="button"
                        onclick="document.getElementById('add-departure-panel').classList.toggle('hidden')"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                    + {{ __('messages.add_departure') }}
                </button>
            @else
                <span class="text-xs text-gray-400 italic">
                    Publish itinerary with at least one day to enable departures.
                </span>
            @endif
        </div>

        @if($service->isItineraryPublished() && $service->itineraryDays->isNotEmpty())
            <div id="add-departure-panel" class="hidden bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                <form method="POST" action="{{ route('provider.services.departures.store', $service) }}">
                    @csrf
                    @include('provider.services.itinerary._departure_form', [
                        'departure'   => null,
                        'submitLabel' => __('messages.add_departure'),
                    ])
                </form>
            </div>
        @endif

        @if($errors->has('departure'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded mb-3 text-sm">
                {{ $errors->first('departure') }}
            </div>
        @endif

        @if($service->departures->isEmpty())
            <p class="text-gray-500 text-center py-8 text-sm">{{ __('messages.no_departures_yet') }}</p>
        @else
            <div class="space-y-2">
                @foreach($service->departures->sortBy('start_date') as $departure)
                    @include('provider.services.itinerary._departure_row', [
                        'departure' => $departure,
                        'service'   => $service,
                    ])
                @endforeach
            </div>
        @endif
    </div>

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

    // PROVIDER-ITINERARY-08: Waypoint searchable picker (vanilla JS)
    document.querySelectorAll('.waypoint-picker').forEach(function (picker) {
        var display   = picker.querySelector('.picker-display');
        var valueField = picker.querySelector('.picker-value');
        var results   = picker.querySelector('.picker-results');
        var searchUrl = picker.dataset.searchUrl;
        var debounceTimer = null;
        var requestToken = 0;

        function clearResults() {
            results.innerHTML = '';
            results.classList.add('hidden');
        }

        function showResults(items) {
            if (!items || items.length === 0) {
                clearResults();
                return;
            }
            results.innerHTML = '';
                        items.forEach(function (item) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'block w-full text-left px-3 py-2 text-sm hover:bg-gray-50 border-b border-gray-100 last:border-b-0';
                btn.textContent = item.name;

                // Use mousedown + preventDefault to prevent input blur from
                // hiding dropdown before click registers.
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    valueField.value = item.id;
                    display.value = item.name;
                    clearResults();
                });

                results.appendChild(btn);
            });
            results.classList.remove('hidden');
        }

        display.addEventListener('input', function () {
            var q = display.value.trim();
            if (q.length < 2) {
                clearResults();
                return;
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                var myToken = ++requestToken;
                fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (myToken !== requestToken) return;
                    showResults(data.results || []);
                })
                .catch(function () {
                    if (myToken !== requestToken) return;
                    clearResults();
                });
            }, 250);
        });

        display.addEventListener('blur', function () {
            // If provider clears the field, unset the hidden ID
            if (display.value.trim() === '') {
                valueField.value = '';
            }
            // Delay to allow click on a result to fire first
            setTimeout(clearResults, 180);
        });

        display.addEventListener('focus', function () {
            if (display.value.trim().length >= 2 && results.children.length > 0) {
                results.classList.remove('hidden');
            }
        });
    });
</script>
@endsection