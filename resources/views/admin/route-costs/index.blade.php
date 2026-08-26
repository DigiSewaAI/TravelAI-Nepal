@extends('layouts.admin')
@section('title', 'Manage Route Costs')
@section('header', 'Route Costs')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Route Costs</h2>
    <a href="{{ route('admin.route-costs.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Add Cost</a>
</div>
<table class="min-w-full bg-white border rounded-lg">
    <thead>
        <tr class="bg-gray-100">
            <th>ID</th><th>Route</th><th>Type</th><th>Name</th><th>Amount</th><th>Currency</th><th>Effective</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($costs as $cost)
        <tr class="border-t">
            <td class="px-4 py-2">{{ $cost->id }}</td>
            <td class="px-4 py-2">{{ $cost->route->name ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ $cost->type }}</td>
            <td class="px-4 py-2">{{ $cost->name ?? '—' }}</td>
            <td class="px-4 py-2">{{ $cost->amount }}</td>
            <td class="px-4 py-2">{{ $cost->currency }}</td>
            <td class="px-4 py-2">{{ $cost->effective_from->format('Y-m-d') }}</td>
            <td class="px-4 py-2">
                <a href="{{ route('admin.route-costs.edit', $cost) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                <form action="{{ route('admin.route-costs.destroy', $cost) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="px-4 py-4 text-center text-gray-500">No cost entries.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection