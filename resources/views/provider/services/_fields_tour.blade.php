{{-- Phase 4M-2-3+4: Tour category fields --}}
@php $detail = $service?->tourDetail; @endphp

<div id="fields-tour" class="category-fields hidden space-y-4" data-slug="tour">

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.duration_days') }} *</label>
        <input type="number" name="duration_days" value="{{ old('duration_days', $detail?->duration_days ?? '') }}" min="1"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_days') border-red-500 @enderror">
        @error('duration_days')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.max_pax') }}</label>
        <input type="number" name="max_pax" value="{{ old('max_pax', $detail?->max_pax ?? '') }}" min="1"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('max_pax') border-red-500 @enderror">
        @error('max_pax')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

</div>