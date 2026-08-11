@extends('layouts.admin')

@section('title', 'Services | TravelAI Nepal')
@section('header', 'Manage Services')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">All Services</h2>

    @if($services->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Name</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Provider</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Category</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Price</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium">{{ $service->name }}</td>
                        <td class="py-3 text-sm">{{ $service->provider->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">{{ $service->category->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">Rs. {{ number_format($service->price, 0) }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($service->status === 'active') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <!-- View -->
                            <a href="{{ route('admin.services.show', $service) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>

                            <!-- Toggle Status (Active/Inactive) -->
<form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="inline mr-2">
    @csrf
    <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Toggle Status">
        <i class="fas fa-toggle-on"></i>
    </button>
</form>

                            <!-- Delete -->
                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline"
                                  onsubmit="return confirm('Delete this service?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
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
            {{ $services->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No services found.</p>
    @endif
</div>
@endsection