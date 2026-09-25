{{-- Phase 4M-2-3+4: Hotel category fields --}}
@php $detail = $service?->hotelDetail; @endphp

<div id="fields-hotel" class="category-fields hidden space-y-4" data-slug="hotel">

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.room_count') }}</label>
        <input type="number" name="room_count" value="{{ old('room_count', $detail?->room_count ?? '') }}" min="0"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('room_count') border-red-500 @enderror">
        @error('room_count')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.star_rating') }}</label>
        <input type="number" name="star_rating" value="{{ old('star_rating', $detail?->star_rating ?? '') }}" min="1" max="5"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('star_rating') border-red-500 @enderror">
        @error('star_rating')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.amenities') }}</label>
        <input type="text" name="amenities"
               value="{{ old('amenities', is_array($detail?->amenities) ? implode(', ', $detail->amenities) : '') }}"
               placeholder="WiFi, Parking, Pool"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amenities') border-red-500 @enderror">
        <p class="text-xs text-gray-400 mt-1">Separate with commas</p>
        @error('amenities')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.check_in_time') }}</label>
        <input type="time" name="check_in_time" value="{{ old('check_in_time', $detail?->check_in_time ?? '') }}"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_in_time') border-red-500 @enderror">
        @error('check_in_time')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.check_out_time') }}</label>
        <input type="time" name="check_out_time" value="{{ old('check_out_time', $detail?->check_out_time ?? '') }}"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('check_out_time') border-red-500 @enderror">
        @error('check_out_time')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

</div>