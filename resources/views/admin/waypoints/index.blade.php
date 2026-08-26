@extends('layouts.admin')
@section('title', 'Manage Waypoints')
@section('header', 'Waypoints')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Waypoints</h2>
    <a href="{{ route('admin.waypoints.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Waypoint</a>
</div>
<table class="min-w-full bg-white border rounded-lg">
    <thead>
        <tr class="bg-gray-100">
            <th>ID</th><th>Name</th><th>Type</th><th>Altitude</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($waypoints as $wp)
        <tr class="border-t">
            <td class="px-4 py-2">{{ $wp->id }}</td>
            <td class="px-4 py-2">{{ $wp->name }}</td>
            <td class="px-4 py-2">{{ $wp->type }}</td>
            <td class="px-4 py-2">{{ $wp->altitude ?? 'N/A' }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('admin.waypoints.edit', $wp) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                <form action="{{ route('admin.waypoints.destroy', $wp) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No waypoints.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection