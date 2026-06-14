@extends('agency.layouts.app')

@section('title', 'Create Trek')
@section('header', 'Add New Trek')

@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
    <form method="POST" action="{{ route('agency.treks.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Trek Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-blue-500">
            @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Duration (days)</label>
                <input type="number" name="duration_days" value="{{ old('duration_days') }}" required class="w-full border rounded-lg px-3 py-2">
                @error('duration_days') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Difficulty</label>
                <select name="difficulty" required class="w-full border rounded-lg px-3 py-2">
                    <option value="">Select</option>
                    <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="moderate" {{ old('difficulty') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
                @error('difficulty') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Price (USD)</label>
            <input type="number" step="0.01" name="price" value="{{ old('price') }}" required class="w-full border rounded-lg px-3 py-2">
            @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Itinerary (JSON array, optional)</label>
            <textarea name="itinerary" rows="4" class="w-full border rounded-lg px-3 py-2" placeholder='["Day 1: Arrival", "Day 2: Trek to ABC"]'>{{ old('itinerary') }}</textarea>
            <p class="text-gray-400 text-xs mt-1">Enter valid JSON array of strings.</p>
            @error('itinerary') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Image fields -->
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Cover Image</label>
            <input type="file" name="cover_image" class="w-full border rounded-lg px-3 py-2">
            @error('cover_image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Gallery Images (multiple)</label>
            <input type="file" name="gallery[]" multiple class="w-full border rounded-lg px-3 py-2">
            @error('gallery.*') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Save Trek</button>
            <a href="{{ route('agency.treks.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>
@endsection