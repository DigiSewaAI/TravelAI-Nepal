@extends('layouts.admin')
@section('title', 'Create Waypoint')
@section('header', 'Create Waypoint')
@section('content')
<form method="POST" action="{{ route('admin.waypoints.store') }}" class="bg-white p-6 rounded-lg shadow">
    @csrf
    <div class="mb-4">
        <label class="block font-medium mb-1">Name *</label>
        <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name') }}" required>
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Type *</label>
        <select name="type" class="w-full border rounded-lg px-4 py-2" required>
            <option value="village">Village</option>
            <option value="checkpoint">Checkpoint</option>
            <option value="landmark">Landmark</option>
            <option value="pass">Pass</option>
            <option value="peak">Peak</option>
            <option value="trailhead">Trailhead</option>
        </select>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block font-medium mb-1">Latitude</label>
            <input type="number" step="0.00000001" name="latitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('latitude') }}">
        </div>
        <div>
            <label class="block font-medium mb-1">Longitude</label>
            <input type="number" step="0.00000001" name="longitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('longitude') }}">
        </div>
        <div>
            <label class="block font-medium mb-1">Altitude (m)</label>
            <input type="number" name="altitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('altitude') }}" min="0">
        </div>
    </div>
    <div class="mt-4">
        <label class="block font-medium mb-1">Description</label>
        <textarea name="description" class="w-full border rounded-lg px-4 py-2" rows="3">{{ old('description') }}</textarea>
    </div>
    <div class="mt-4">
        <label class="block font-medium mb-1">Metadata (JSON)</label>
        <textarea name="metadata" class="w-full border rounded-lg px-4 py-2" rows="2" placeholder='{"key":"value"}'>{{ old('metadata') }}</textarea>
    </div>
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Save</button>
        <a href="{{ route('admin.waypoints.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </div>
</form>
@endsection