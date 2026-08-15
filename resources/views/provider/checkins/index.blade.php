@extends('layouts.provider')

@section('title', 'Check-ins | TravelAI Nepal')
@section('header', '📍 Check-ins')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <!-- Search & Filters -->
    <div class="flex flex-wrap gap-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-center w-full">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" placeholder="Search by traveler or service..." 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('provider.checkins.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                Reset
            </a>
        </form>
    </div>

    @if($checkins->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Traveler</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Service</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Checkpoint</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Time</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checkins as $scan)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium">
                            {{ $scan->booking->traveler->name ?? 'Guest' }}
                        </td>
                        <td class="py-3 text-sm">{{ $scan->booking->service->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">
                            <i class="fas fa-map-pin text-blue-500 mr-1"></i>
                            {{ $scan->checkpoint_name ?? 'Check-in' }}
                        </td>
                        <td class="py-3 text-sm">{{ $scan->scanned_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('provider.checkins.show', $scan) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $checkins->appends(request()->query())->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No check-ins found.</p>
    @endif
</div>
@endsection