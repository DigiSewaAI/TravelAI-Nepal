@extends('layouts.admin')

@section('title', 'Subscriptions | TravelAI Nepal')
@section('header', 'Manage Subscriptions')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">All Subscriptions</h2>

    @if($subscriptions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Provider</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Plan</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Start Date</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">End Date</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscriptions as $subscription)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm">{{ $subscription->provider->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $subscription->plan->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d') : 'N/A' }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($subscription->status === 'active') bg-green-100 text-green-800
                                @elseif($subscription->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.subscriptions.destroy', $subscription) }}" class="inline"
                                  onsubmit="return confirm('Delete this subscription?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $subscriptions->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No subscriptions found.</p>
    @endif
</div>
@endsection