@extends('layouts.admin')

@section('title', 'Provider Reports | TravelAI Nepal')
@section('header', 'Provider Reports')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $total }}</p>
            <p class="text-xs text-gray-500">Total Providers</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-2xl font-bold text-green-600">{{ $verified }}</p>
            <p class="text-xs text-gray-500">Verified</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $pending }}</p>
            <p class="text-xs text-gray-500">Pending</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-2xl font-bold text-red-600">{{ $rejected }}</p>
            <p class="text-xs text-gray-500">Rejected</p>
        </div>
    </div>

    @if($providers->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Provider</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Owner</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Type</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 text-sm font-medium">{{ $provider->name }}</td>
                        <td class="py-2 text-sm">{{ $provider->user->name ?? 'N/A' }}</td>
                        <td class="py-2 text-sm">
                            @foreach($provider->types as $type)
                                <span class="px-1 py-0.5 text-xs rounded bg-blue-100 text-blue-700 mr-1">{{ $type->name }}</span>
                            @endforeach
                        </td>
                        <td class="py-2 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($provider->verification_status === 'verified') bg-green-100 text-green-800
                                @elseif($provider->verification_status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($provider->verification_status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($provider->verification_status) }}
                            </span>
                        </td>
                        <td class="py-2 text-sm">{{ $provider->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $providers->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No providers found.</p>
    @endif
</div>
@endsection