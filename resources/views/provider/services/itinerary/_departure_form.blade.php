@php
    $isEdit = isset($departure) && $departure;
    $startVal = old('start_date', $isEdit ? $departure->start_date->format('Y-m-d') : '');
    $endVal   = old('end_date',   $isEdit ? $departure->end_date->format('Y-m-d') : '');
    $capVal   = old('capacity',   $isEdit ? $departure->capacity : '');
    $submit   = $submitLabel ?? __('messages.add_departure');
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('messages.start_date') }} *</label>
        <input type="date" name="start_date" value="{{ $startVal }}" required
               min="{{ date('Y-m-d') }}"
               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        @error('start_date')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('messages.end_date') }} *</label>
        <input type="date" name="end_date" value="{{ $endVal }}" required
               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        @error('end_date')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('messages.capacity') }} *</label>
        <input type="number" name="capacity" value="{{ $capVal }}" required min="1"
               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        @error('capacity')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-3">
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg">
        {{ $submit }}
    </button>
</div>