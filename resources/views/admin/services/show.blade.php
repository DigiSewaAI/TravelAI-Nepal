@extends('layouts.admin')

@section('title', 'Service Details | TravelAI Nepal')
@section('header', 'Service Details')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('admin.services.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Services
        </a>
    </div>

    <h2 class="text-2xl font-bold text-gray-900">{{ $service->name }}</h2>
    <div class="flex gap-2 mt-2">
        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ $service->category->name ?? 'N/A' }}</span>
        <span class="px-2 py-1 text-xs rounded-full
            @if($service->status === 'active') bg-green-100 text-green-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst($service->status) }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4 border-t pt-4">
        <div>
            <p class="text-sm text-gray-500">Provider</p>
            <p class="font-medium">{{ $service->provider->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Price</p>
            <p class="font-medium">Rs. {{ number_format($service->price, 0) }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Created</p>
            <p class="font-medium">{{ $service->created_at->format('M d, Y') }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Service ID</p>
            <p class="font-medium">#{{ $service->id }}</p>
        </div>
    </div>

    @if($service->description)
        <div class="mt-4 border-t pt-4">
            <h3 class="font-semibold text-gray-700">Description</h3>
            <p class="text-gray-600 mt-1">{{ $service->description }}</p>
        </div>
    @endif

    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.services.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="inline">
            @csrf
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-toggle-on mr-1"></i> Toggle Status
            </button>
        </form>
    </div>
</div>
@endsection