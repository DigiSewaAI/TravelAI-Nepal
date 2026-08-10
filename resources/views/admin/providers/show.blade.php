@extends('layouts.admin')

@section('title', 'Provider Details | TravelAI Nepal')
@section('header', 'Provider Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('admin.providers.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Providers
        </a>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Provider Info -->
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center gap-4 mb-4">
                @if($provider->logo_url)
                    <img src="{{ asset('storage/' . $provider->logo_url) }}" class="w-16 h-16 rounded-full object-cover">
                @else
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-building text-blue-600 text-2xl"></i>
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $provider->name }}</h2>
                    <p class="text-gray-500 text-sm">{{ $provider->slug }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-500">Owner:</span> {{ $provider->user->name ?? 'N/A' }}</div>
                <div><span class="text-gray-500">Email:</span> {{ $provider->contact_email ?? 'N/A' }}</div>
                <div><span class="text-gray-500">Phone:</span> {{ $provider->contact_phone ?? 'N/A' }}</div>
                <div><span class="text-gray-500">Address:</span> {{ $provider->address ?? 'N/A' }}</div>
                <div><span class="text-gray-500">Verification:</span>
                    <span class="px-2 py-1 rounded-full text-xs
                        @if($provider->verification_status === 'verified') bg-green-100 text-green-800
                        @elseif($provider->verification_status === 'rejected') bg-red-100 text-red-800
                        @elseif($provider->verification_status === 'under_review') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($provider->verification_status) }}
                    </span>
                </div>
                <div><span class="text-gray-500">Status:</span>
                    <span class="px-2 py-1 rounded-full text-xs
                        @if($provider->is_active) bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ $provider->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            @if($provider->description)
                <div class="mt-4 pt-4 border-t">
                    <h3 class="font-semibold text-gray-700 text-sm">Description</h3>
                    <p class="text-gray-600 text-sm mt-1">{{ $provider->description }}</p>
                </div>
            @endif

            <!-- Provider Types -->
            <div class="mt-4 pt-4 border-t">
                <h3 class="font-semibold text-gray-700 text-sm">Provider Types</h3>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($provider->types as $type)
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ $type->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-xl shadow-sm border p-6 h-fit">
            <h3 class="font-semibold text-gray-700 mb-4">Actions</h3>

            <!-- Verification Status Update -->
            <form method="POST" action="{{ route('admin.providers.verify', $provider) }}" class="mb-4">
                @csrf
                @method('PATCH')
                <div class="space-y-2">
                    <label class="block text-sm text-gray-600">Update Verification Status</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="pending" {{ $provider->verification_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="under_review" {{ $provider->verification_status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="verified" {{ $provider->verification_status === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ $provider->verification_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">
                        Update Status
                    </button>
                </div>
            </form>

            <!-- Toggle Active -->
            <form method="POST" action="{{ route('admin.providers.toggle', $provider) }}" class="mb-4">
                @csrf
                @method('PATCH')
                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-toggle-on mr-1"></i>
                    {{ $provider->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>

            <!-- Delete -->
            <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}"
                  onsubmit="return confirm('Delete this provider permanently? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition">
                    <i class="fas fa-trash mr-1"></i> Delete Provider
                </button>
            </form>
        </div>
    </div>

    <!-- Documents -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Verification Documents</h3>
        @if($provider->documents && $provider->documents->count() > 0)
            <div class="grid md:grid-cols-2 gap-3">
                @foreach($provider->documents as $doc)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <span class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full ml-2
                                @if($doc->status === 'approved') bg-green-100 text-green-800
                                @elseif($doc->status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($doc->status) }}
                            </span>
                        </div>
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                           class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">No documents uploaded.</p>
        @endif
    </div>

    <!-- Services -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Services ({{ $provider->services->count() }})</h3>
        @if($provider->services && $provider->services->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($provider->services as $service)
                    <div class="border rounded-lg p-3 text-center">
                        <div class="text-sm font-medium text-gray-800">{{ $service->name }}</div>
                        <div class="text-xs text-gray-500">${{ number_format($service->price, 0) }}</div>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            @if($service->status === 'active') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($service->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">No services yet.</p>
        @endif
    </div>
</div>
@endsection