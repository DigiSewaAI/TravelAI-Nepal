@extends('agency.layouts.app')

@section('title', 'Edit Trek')
@section('header', 'Edit Trek')

@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('agency.treks.update', $trek) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Trek Name</label>
            <input type="text" name="name" value="{{ old('name', $trek->name) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Duration (days)</label>
                <input type="number" name="duration_days" value="{{ old('duration_days', $trek->duration_days) }}" required class="w-full border rounded-lg px-3 py-2">
                @error('duration_days') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Difficulty</label>
                <select name="difficulty" required class="w-full border rounded-lg px-3 py-2">
                    <option value="easy" {{ (old('difficulty', $trek->difficulty) == 'easy') ? 'selected' : '' }}>Easy</option>
                    <option value="moderate" {{ (old('difficulty', $trek->difficulty) == 'moderate') ? 'selected' : '' }}>Moderate</option>
                    <option value="hard" {{ (old('difficulty', $trek->difficulty) == 'hard') ? 'selected' : '' }}>Hard</option>
                </select>
                @error('difficulty') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Price (USD)</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $trek->price) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Itinerary field – correctly decoded and pretty-printed --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Itinerary (JSON array)</label>
            @php
                $currentItinerary = old('itinerary');
                if (is_null($currentItinerary) && $trek->itinerary) {
                    $decoded = json_decode($trek->itinerary, true);
                    $currentItinerary = $decoded ? json_encode($decoded, JSON_PRETTY_PRINT) : '[]';
                } elseif (is_null($currentItinerary)) {
                    $currentItinerary = '[]';
                }
            @endphp
            <textarea name="itinerary" rows="6" class="w-full border rounded-lg px-3 py-2 font-mono text-sm">{{ $currentItinerary }}</textarea>
            <p class="text-gray-400 text-xs mt-1">Valid JSON array of strings. Example: <code>["Day 1: Arrival", "Day 2: Trek to ABC"]</code></p>
            @error('itinerary') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Cover Image --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Cover Image</label>
            @if($trek->cover_image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $trek->cover_image) }}" class="h-24 w-auto rounded border">
                </div>
            @endif
            <input type="file" name="cover_image" class="w-full border rounded-lg px-3 py-2">
            <p class="text-gray-400 text-xs mt-1">Leave empty to keep current cover image.</p>
            @error('cover_image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Gallery Images --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Gallery Images (multiple)</label>
            @if($trek->gallery)
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(json_decode($trek->gallery, true) as $img)
                        <img src="{{ asset('storage/' . $img) }}" class="h-16 w-16 object-cover rounded border">
                    @endforeach
                </div>
            @endif
            <input type="file" name="gallery[]" multiple class="w-full border rounded-lg px-3 py-2">
            <p class="text-gray-400 text-xs mt-1">Upload new images to replace the entire gallery.</p>
            @error('gallery.*') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Update Trek</button>
            <a href="{{ route('agency.treks.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>
@endsection