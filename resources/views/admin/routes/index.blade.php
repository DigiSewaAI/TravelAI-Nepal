@extends('layouts.admin')
@section('title', 'Manage Routes')
@section('header', 'Manage Routes')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Routes</h2>
    <a href="{{ route('admin.routes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Route</a>
</div>
<table class="min-w-full bg-white border rounded-lg">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">ID</th>
            <th class="px-4 py-2 text-left">Name</th>
            <th class="px-4 py-2 text-left">Days</th>
            <th class="px-4 py-2 text-left">Difficulty</th>
            <th class="px-4 py-2 text-left">Active</th>
            <th class="px-4 py-2 text-left">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($routes as $route)
        <tr class="border-t">
            <td class="px-4 py-2">{{ $route->id }}</td>
            <td class="px-4 py-2">{{ $route->name }}</td>
            <td class="px-4 py-2">{{ $route->duration_days }}</td>
            <td class="px-4 py-2">{{ $route->difficulty }}</td>
            <td class="px-4 py-2">{{ $route->is_active ? '✅' : '❌' }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('admin.routes.edit', $route) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                <form action="{{ route('admin.routes.destroy', $route) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete this route?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-4 py-4 text-center text-gray-500">No routes found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection