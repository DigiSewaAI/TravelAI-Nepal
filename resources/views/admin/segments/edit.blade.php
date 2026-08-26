@extends('layouts.admin')
@section('title', 'Edit Segment')
@section('header', 'Edit Segment')
@section('content')
<form method="POST" action="{{ route('admin.segments.update', $segment) }}" class="bg-white p-6 rounded-lg shadow">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium mb-1">Route *</label>
            <select name="route_id" class="w-full border rounded-lg px-4 py-2" required>
                @foreach($routes as $id => $name)
                <option value="{{ $id }}" {{ old('route_id', $segment->route_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-medium mb-1">Sequence *</label>
            <input type="number" name="sequence" class="w-full border rounded-lg px-4 py-2" value="{{ old('sequence', $segment->sequence) }}" min="0" required>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">From Waypoint *</label>
            <select name="from_waypoint_id" class="w-full border rounded-lg px-4 py-2" required>
                @foreach($waypoints as $id => $name)
                <option value="{{ $id }}" {{ old('from_waypoint_id', $segment->from_waypoint_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block font-medium mb-1">To Waypoint *</label>
            <select name="to_waypoint_id" class="w-full border rounded-lg px-4 py-2" required>
                @foreach($waypoints as $id => $name)
                <option value="{{ $id }}" {{ old('to_waypoint_id', $segment->to_waypoint_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-4 mt-4">
        <div>
            <label class="block font-medium mb-1">Distance (km) *</label>
            <input type="number" step="0.01" name="distance_km" class="w-full border rounded-lg px-4 py-2" value="{{ old('distance_km', $segment->distance_km) }}" required>
        </div>
        <div>
            <label class="block font-medium mb-1">Est. Time (hours)</label>
            <input type="number" step="0.1" name="estimated_time_hours" class="w-full border rounded-lg px-4 py-2" value="{{ old('estimated_time_hours', $segment->estimated_time_hours) }}">
        </div>
        <div>
            <label class="block font-medium mb-1">Elevation Gain (m)</label>
            <input type="number" name="elevation_gain_m" class="w-full border rounded-lg px-4 py-2" value="{{ old('elevation_gain_m', $segment->elevation_gain_m) }}" min="0">
        </div>
    </div>
    <div class="mt-4">
        <label class="block font-medium mb-1">Elevation Loss (m)</label>
        <input type="number" name="elevation_loss_m" class="w-full border rounded-lg px-4 py-2" value="{{ old('elevation_loss_m', $segment->elevation_loss_m) }}" min="0">
    </div>
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Update</button>
        <a href="{{ route('admin.segments.index') }}" class="ml-2 text-gray-600 hover:underline">Cancel</a>
    </div>
</form>
@endsection