{{-- Phase 4M-2-3+4: Trek category fields --}}
@php $detail = $service?->trekDetail; @endphp

<div id="fields-trek" class="category-fields hidden space-y-4" data-slug="trek">

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.duration_days') }} *</label>
        <input type="number" name="duration_days" value="{{ old('duration_days', $detail?->duration_days ?? '') }}" min="1"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('duration_days') border-red-500 @enderror">
        @error('duration_days')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.difficulty') }} *</label>
        <select name="difficulty" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('difficulty') border-red-500 @enderror">
            <option value="">-- {{ __('messages.difficulty') }} --</option>
            <option value="easy" {{ old('difficulty', $detail?->difficulty ?? '') == 'easy' ? 'selected' : '' }}>Easy</option>
            <option value="moderate" {{ old('difficulty', $detail?->difficulty ?? '') == 'moderate' ? 'selected' : '' }}>Moderate</option>
            <option value="hard" {{ old('difficulty', $detail?->difficulty ?? '') == 'hard' ? 'selected' : '' }}>Hard</option>
        </select>
        @error('difficulty')
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

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.max_altitude') }}</label>
        <input type="number" name="max_altitude" value="{{ old('max_altitude', $detail?->max_altitude ?? '') }}" min="0"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('max_altitude') border-red-500 @enderror">
        @error('max_altitude')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.season') }}</label>
        <input type="text" name="season" value="{{ old('season', $detail?->season ?? '') }}" placeholder="Spring, Autumn"
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('season') border-red-500 @enderror">
        @error('season')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

</div>