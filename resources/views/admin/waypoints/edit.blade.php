@extends('layouts.admin')
@section('title', 'Edit Waypoint')
@section('header', 'Edit Waypoint')
@section('content')
<form method="POST" action="{{ route('admin.waypoints.update', $waypoint) }}" class="bg-white p-6 rounded-lg shadow">
    @csrf
    @method('PUT')
    <div class="mb-4">
        <label class="block font-medium mb-1">Name *</label>
        <input type="text" name="name" class="w-full border rounded-lg px-4 py-2" value="{{ old('name', $waypoint->name) }}" required>
    </div>
    <div class="mb-4">
        <label class="block font-medium mb-1">Type *</label>
        <select name="type" class="w-full border rounded-lg px-4 py-2" required>
            @foreach(['village','checkpoint','landmark','pass','peak','trailhead'] as $type)
            <option value="{{ $type }}" {{ old('type', $waypoint->type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block font-medium mb-1">Latitude</label>
            <input type="number" step="0.00000001" name="latitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('latitude', $waypoint->latitude) }}">
        </div>
        <div>
            <label class="block font-medium mb-1">Longitude</label>
            <input type="number" step="0.00000001" name="longitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('longitude', $waypoint->longitude) }}">
        </div>
        <div>
            <label class="block font-medium mb-1">Altitude (m)</label>
            <input type="number" name="altitude" class="w-full border rounded-lg px-4 py-2" value="{{ old('altitude', $waypoint->altitude) }}" min="0">
        </div>
    </div>
    <div class="mt-4">
        <label class="block font-medium mb-1">Description</label>
        <textarea name="description" class="w-full border rounded-lg px-4 py-2" rows="3">{{ old('description', $waypoint->description) }}</textarea>
    </div>
    <div class="mt-4">
        <label class="block font-medium mb-1">Metadata (JSON)</label>
        <textarea name="metadata" class="w-full border rounded-lg px-4 py-2" rows="2" placeholder='{"key":"value"}'>{{ old('metadata', json_encode($waypoint->metadata)) }}</textarea>
    </div>
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update</button>
        <a href="{{ route('admin.waypoints.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </div>
</form>
@endsection