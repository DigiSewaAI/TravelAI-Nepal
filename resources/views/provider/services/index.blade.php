@extends('layouts.provider')

@section('title', 'Services | TravelAI Nepal')
@section('header', 'Manage Services')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Your Services</h2>
        <a href="{{ route('provider.services.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-plus mr-1"></i> Add New Service
        </a>
    </div>

    @if($services && $services->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Name</th>
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
                        <td class="py-3 text-sm">{{ $service->category->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm">Rs. {{ number_format($service->price, 0) }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs
                                @if($service->status === 'active') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($service->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('provider.services.edit', $service) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('provider.services.destroy', $service) }}" class="inline"
                                  onsubmit="return confirm('Delete this service?')">
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
    @else
        <p class="text-gray-500 text-center py-8">No services yet. Click "Add New Service" to create one.</p>
    @endif
</div>
@endsection