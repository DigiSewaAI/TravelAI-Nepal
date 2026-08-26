@extends('layouts.admin')
@section('title', 'Manage Segments')
@section('header', 'Segments')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Route Segments</h2>
    <a href="{{ route('admin.segments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Segment</a>
</div>
<table class="min-w-full bg-white border rounded-lg">
    <thead>
        <tr class="bg-gray-100">
            <th>ID</th><th>Route</th><th>From</th><th>To</th><th>Dist (km)</th><th>Time (hr)</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($segments as $seg)
        <tr class="border-t">
            <td class="px-4 py-2">{{ $seg->id }}</td>
            <td class="px-4 py-2">{{ $seg->route->name ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $seg->fromWaypoint->name ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $seg->toWaypoint->name ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $seg->distance_km }}</td>
            <td class="px-4 py-2">{{ $seg->estimated_time_hours }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('admin.segments.edit', $seg) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                <form action="{{ route('admin.segments.destroy', $seg) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-4 py-4 text-center text-gray-500">No segments.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection