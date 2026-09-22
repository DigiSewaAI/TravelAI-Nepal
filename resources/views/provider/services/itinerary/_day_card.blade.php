<details class="day-card bg-white rounded-xl shadow-sm border"
         id="day-{{ $day->id }}"
         data-day-id="{{ $day->id }}"
         {{ $day->day_number === 1 ? 'open' : '' }}>

    {{-- Header (sticky, clickable to collapse) --}}
    <summary class="cursor-pointer p-4 hover:bg-gray-50 rounded-t-xl day-card-summary list-none">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-500 uppercase">Day {{ $day->day_number }}</span>
                <h3 class="text-lg font-semibold text-gray-900">{{ $day->title }}</h3>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); moveDay({{ $day->id }}, 'up')"
                        class="text-gray-500 hover:text-gray-800 px-2 py-1 text-sm">↑</button>
                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); moveDay({{ $day->id }}, 'down')"
                        class="text-gray-500 hover:text-gray-800 px-2 py-1 text-sm">↓</button>
                <form method="POST"
                      action="{{ route('provider.services.itinerary.days.destroy', [$service, $day]) }}"
                      class="inline"
                      onsubmit="event.stopPropagation(); return confirm('Delete this day and all its items/media?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="event.stopPropagation()" class="text-red-500 hover:text-red-700 px-2 py-1 text-sm">✕</button>
                </form>
            </div>
        </div>
    </summary>

    <div class="p-5">

    {{-- Day Update Form --}}
    <form method="POST"
          action="{{ route('provider.services.itinerary.days.update', [$service, $day]) }}"
          class="space-y-3">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input type="text" name="title" value="{{ old('title', $day->title) }}" required maxlength="255"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $day->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Distance (km)</label>
                <input type="number" step="0.01" min="0" name="distance_km"
                       value="{{ old('distance_km', $day->distance_km) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Time (hours)</label>
                <input type="number" step="0.1" min="0" name="estimated_time_hours"
                       value="{{ old('estimated_time_hours', $day->estimated_time_hours) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Elevation Gain (m)</label>
                <input type="number" min="0" name="elevation_gain_m"
                       value="{{ old('elevation_gain_m', $day->elevation_gain_m) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Elevation Loss (m)</label>
                <input type="number" min="0" name="elevation_loss_m"
                       value="{{ old('elevation_loss_m', $day->elevation_loss_m) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Altitude (m)</label>
                <input type="number" name="altitude_m"
                       value="{{ old('altitude_m', $day->altitude_m) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Accommodation</label>
                <input type="text" name="accommodation" maxlength="255"
                       value="{{ old('accommodation', $day->accommodation) }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Meals Included</label>
            @php $meals = $day->meals_included ?? []; @endphp
            <div class="flex gap-4">
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="meals_included[]" value="B"
                           {{ in_array('B', $meals) ? 'checked' : '' }} class="mr-1"> Breakfast
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="meals_included[]" value="L"
                           {{ in_array('L', $meals) ? 'checked' : '' }} class="mr-1"> Lunch
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="meals_included[]" value="D"
                           {{ in_array('D', $meals) ? 'checked' : '' }} class="mr-1"> Dinner
                </label>
            </div>
        </div>

        {{-- PROVIDER-ITINERARY-08: Waypoint pickers (searchable, server-side) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Waypoint</label>
                <div class="relative waypoint-picker" data-search-url="{{ route('provider.services.itinerary.waypoints.search', $service) }}">
                    <input type="text"
                           class="w-full px-3 py-2 border rounded-lg text-sm picker-display"
                           placeholder="Search waypoint..."
                           value="{{ $day->startWaypoint?->name ?? '' }}"
                           autocomplete="off">
                    <input type="hidden" name="start_waypoint_id"
                           class="picker-value"
                           value="{{ old('start_waypoint_id', $day->start_waypoint_id) }}">
                    <div class="picker-results hidden absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Overnight Waypoint</label>
                <div class="relative waypoint-picker" data-search-url="{{ route('provider.services.itinerary.waypoints.search', $service) }}">
                    <input type="text"
                           class="w-full px-3 py-2 border rounded-lg text-sm picker-display"
                           placeholder="Search waypoint..."
                           value="{{ $day->overnightWaypoint?->name ?? '' }}"
                           autocomplete="off">
                    <input type="hidden" name="overnight_waypoint_id"
                           class="picker-value"
                           value="{{ old('overnight_waypoint_id', $day->overnight_waypoint_id) }}">
                    <div class="picker-results hidden absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Waypoint</label>
                <div class="relative waypoint-picker" data-search-url="{{ route('provider.services.itinerary.waypoints.search', $service) }}">
                    <input type="text"
                           class="w-full px-3 py-2 border rounded-lg text-sm picker-display"
                           placeholder="Search waypoint..."
                           value="{{ $day->endWaypoint?->name ?? '' }}"
                           autocomplete="off">
                    <input type="hidden" name="end_waypoint_id"
                           class="picker-value"
                           value="{{ old('end_waypoint_id', $day->end_waypoint_id) }}">
                    <div class="picker-results hidden absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                </div>
            </div>
        </div>

        <div class="pt-2 flex gap-2 flex-wrap">
            <button type="submit" name="action" value="save"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                {{ __('messages.save_day') }}
            </button>
            <button type="submit" name="action" value="save_next"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                {{ __('messages.save_next_day') }} →
            </button>
        </div>
    </form>

    {{-- Items Section --}}
    <div class="mt-5 pt-4 border-t border-gray-100">
        <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase">Items</span>
        </div>

        @foreach($day->items as $item)
            @include('provider.services.itinerary._item_row', ['item' => $item, 'day' => $day, 'service' => $service])
        @endforeach

        {{-- Add Item form --}}
        <form method="POST"
              action="{{ route('provider.services.itinerary.items.store', [$service, $day]) }}"
              class="mt-2 flex gap-2 items-start">
            @csrf
            <input type="text" name="title" required maxlength="255" placeholder="New item title..."
                   class="flex-1 px-3 py-2 border rounded-lg text-sm">
            <select name="time_of_day" class="px-3 py-2 border rounded-lg text-sm">
                <option value="">—</option>
                <option value="morning">Morning</option>
                <option value="afternoon">Afternoon</option>
                <option value="evening">Evening</option>
            </select>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-3 py-2 rounded-lg">
                + Add
            </button>
        </form>
    </div>

    {{-- Media Section --}}
    <div class="mt-5 pt-4 border-t border-gray-100">
        <span class="text-xs font-semibold text-gray-500 uppercase">Media</span>

        @if($day->media->isNotEmpty())
            <div class="grid grid-cols-4 gap-2 mt-2">
                @foreach($day->media as $media)
                    @include('provider.services.itinerary._media_row', ['media' => $media, 'service' => $service])
                @endforeach
            </div>
        @endif

        @include('provider.services.itinerary._media_upload', ['day' => $day, 'service' => $service])
    </div>

    </div>{{-- /.p-5 inner --}}
</details>