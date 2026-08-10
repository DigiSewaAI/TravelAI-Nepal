@extends('layouts.admin')

@section('title', 'Manage Providers | TravelAI Nepal')
@section('header', 'Providers')

@section('content')
<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Types</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Services</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($providers as $provider)
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @if($provider->logo_url)
                                <img src="{{ asset('storage/' . $provider->logo_url) }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-building text-blue-600 text-xs"></i>
                                </div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-800">{{ $provider->name }}</div>
                                <div class="text-xs text-gray-400">{{ $provider->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $provider->user->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        @foreach($provider->types as $type)
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 mr-1">{{ $type->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-center">{{ $provider->services_count ?? 0 }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($provider->verification_status === 'verified') bg-green-100 text-green-800
                            @elseif($provider->verification_status === 'rejected') bg-red-100 text-red-800
                            @elseif($provider->verification_status === 'under_review') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($provider->verification_status) }}
                        </span>
                        <span class="ml-1 px-2 py-1 text-xs rounded-full
                            @if($provider->is_active) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ $provider->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.providers.show', $provider) }}" 
                           class="text-blue-600 hover:text-blue-800 mr-2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.providers.toggle', $provider) }}" class="inline mr-2">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                                <i class="fas fa-toggle-on"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" class="inline"
                              onsubmit="return confirm('Delete this provider?')">
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
    <div class="px-6 py-4">
        {{ $providers->links() }}
    </div>
</div>
@endsection