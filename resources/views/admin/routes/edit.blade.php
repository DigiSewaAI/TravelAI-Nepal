@extends('layouts.admin')
@section('title', 'Edit Route')
@section('header', 'Edit Route')
@section('content')
<form method="POST" action="{{ route('admin.routes.update', $route) }}" class="bg-white p-6 rounded-lg shadow">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="block font-medium mb-1">Name *</label>
        <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name', $route->name) }}" required>
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Description</label>
        <textarea name="description" class="w-full border rounded-lg px-4 py-2" rows="4">{{ old('description', $route->description) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium mb-1">Difficulty *</label>
            <select name="difficulty" class="w-full border rounded-lg px-4 py-2" required>
                @foreach(['easy','moderate','hard','extreme'] as $diff)
                <option value="{{ $diff }}" {{ old('difficulty', $route->difficulty) == $diff ? 'selected' : '' }}>{{ ucfirst($diff) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-medium mb-1">Duration (days) *</label>
            <input type="number" name="duration_days" class="w-full border rounded-lg px-4 py-2" value="{{ old('duration_days', $route->duration_days) }}" min="1" required>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">Max Altitude (m)</label>
            <input type="number" name="max_altitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('max_altitude', $route->max_altitude) }}" min="0">
        </div>
        <div>
            <label class="block font-medium mb-1">Season</label>
            <input type="text" name="season" class="w-full border rounded-lg px-4 py-2" value="{{ old('season', $route->season) }}" placeholder="e.g., Spring, Autumn">
        </div>
    </div>
    <div class="mt-4">
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $route->is_active) ? 'checked' : '' }} class="mr-2"> Active
        </label>
    </div>
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update</button>
        <a href="{{ route('admin.routes.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </div>
</form>
@endsection