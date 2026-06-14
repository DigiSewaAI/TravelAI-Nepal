@extends('agency.layouts.app')

@section('title', 'Manage Treks')
@section('header', 'Treks')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('agency.treks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Trek</a>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Difficulty</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($treks as $trek)
            <tr>
                <td class="px-6 py-4">{{ $trek->name }}</td>
                <td class="px-6 py-4">{{ $trek->duration_days }} days</td>
                <td class="px-6 py-4 capitalize">{{ $trek->difficulty }}</td>
                <td class="px-6 py-4">${{ number_format($trek->price, 2) }}</td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('agency.treks.edit', $trek) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form action="{{ route('agency.treks.destroy', $trek) }}" method="POST" class="inline" onsubmit="return confirm('Delete this trek?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No treks added yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection