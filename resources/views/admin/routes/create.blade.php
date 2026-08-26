@extends('layouts.admin')
@section('title', 'Create Route')
@section('header', 'Create Route')
@section('content')
<form method="POST" action="{{ route('admin.routes.store') }}" class="bg-white p-6 rounded-lg shadow">
    @csrf
    <div class="mb-4">
        <label class="block font-medium mb-1">Name *</label>
        <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name') }}" required>
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Description</label>
        <textarea name="description" class="w-full border rounded-lg px-4 py-2" rows="4">{{ old('description') }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium mb-1">Difficulty *</label>
            <select name="difficulty" class="w-full border rounded-lg px-4 py-2" required>
                <option value="easy">Easy</option>
                <option value="moderate">Moderate</option>
                <option value="hard">Hard</option>
                <option value="extreme">Extreme</option>
            </select>
        </div>
        <div>
            <label class="block font-medium mb-1">Duration (days) *</label>
            <input type="number" name="duration_days" class="w-full border rounded-lg px-4 py-2" value="{{ old('duration_days', 1) }}" min="1" required>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">Max Altitude (m)</label>
            <input type="number" name="max_altitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('max_altitude') }}" min="0">
        </div>
        <div>
            <label class="block font-medium mb-1">Season</label>
            <input type="text" name="season" class="w-full border rounded-lg px-4 py-2" value="{{ old('season') }}" placeholder="e.g., Spring, Autumn">
        </div>
    </div>
    <div class="mt-4">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1" checked class="mr-2"> Active
        </label>
    </div>
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Save</button>
        <a href="{{ route('admin.routes.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </div>
</form>
@endsection