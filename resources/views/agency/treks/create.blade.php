@extends('agency.layouts.app')

@section('title', 'Create Trek')
@section('header', 'Add New Trek')

@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('agency.treks.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Trek Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500">
            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Duration (days) *</label>
                <input type="number" name="duration_days" value="{{ old('duration_days') }}" required class="w-full border rounded-lg px-3 py-2">
                @error('duration_days') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Difficulty *</label>
                <select name="difficulty" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Select</option>
                    <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="moderate" {{ old('difficulty') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
                @error('difficulty') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- NEW: Category field --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Category *</label>
            <select name="category" required class="w-full border rounded-lg px-3 py-2">
                <option value="trek" {{ old('category', 'trek') == 'trek' ? 'selected' : '' }}>🏔️ Trek</option>
                <option value="tour" {{ old('category') == 'tour' ? 'selected' : '' }}>🚐 Tour</option>
                <option value="hotel" {{ old('category') == 'hotel' ? 'selected' : '' }}>🏨 Hotel</option>
            </select>
            @error('category') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Price (USD) *</label>
            <input type="number" step="0.01" name="price" value="{{ old('price') }}" required class="w-full border rounded-lg px-3 py-2">
            @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Simple itinerary – one day per line --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Itinerary (one day per line)</label>
            <textarea name="itinerary_lines" rows="6" placeholder="Day 1: Arrival in Kathmandu&#10;Day 2: Drive to Pokhara&#10;Day 3: Start trek..." class="w-full border rounded-lg px-3 py-2"></textarea>
            <p class="text-gray-400 text-xs mt-1">Each line = one day. Leave empty if no itinerary.</p>
            @error('itinerary_lines') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Cover Image --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Cover Image</label>
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/gif" class="w-full border rounded-lg px-3 py-2">
            <p class="text-gray-400 text-xs mt-1">Recommended: 1200x800px, max 2MB (JPG, PNG, GIF).</p>
            @error('cover_image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Gallery Images (multiple) --}}
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Gallery Images (multiple)</label>
            <input type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/jpg,image/gif" class="w-full border rounded-lg px-3 py-2">
            <p class="text-gray-400 text-xs mt-1">Select multiple images. Each max 2MB.</p>
            @error('gallery.*') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save Trek</button>
            <a href="{{ route('agency.treks.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>
@endsection