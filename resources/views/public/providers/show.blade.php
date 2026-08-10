@extends('layouts.public')

@section('title', $provider->name . ' | TravelAI Nepal')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">Explore</a>
        <span class="mx-2">/</span>
        <span>{{ $provider->name }}</span>
    </nav>

    <!-- Provider Profile -->
    <div class="bg-white rounded-2xl shadow-md border p-6">
        <div class="flex items-start gap-6">
            <div>
                @if($provider->logo_url)
                    <img src="{{ asset('storage/' . $provider->logo_url) }}" 
                         class="w-24 h-24 rounded-full object-cover">
                @else
                    <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-building text-4xl text-blue-600"></i>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $provider->name }}</h1>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($provider->types as $type)
                        <span class="px-2 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                            {{ $type->name }}
                        </span>
                    @endforeach
                    @if($provider->verification_status === 'verified')
                        <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-700">
                            <i class="fas fa-check-circle"></i> Verified Provider
                        </span>
                    @endif
                </div>
                @if($provider->description)
                    <p class="text-gray-600 mt-4">{{ $provider->description }}</p>
                @endif
                <div class="mt-4 text-sm text-gray-500">
                    @if($provider->contact_email)
                        <div><i class="fas fa-envelope mr-2"></i> {{ $provider->contact_email }}</div>
                    @endif
                    @if($provider->contact_phone)
                        <div><i class="fas fa-phone mr-2"></i> {{ $provider->contact_phone }}</div>
                    @endif
                    @if($provider->address)
                        <div><i class="fas fa-map-marker-alt mr-2"></i> {{ $provider->address }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Provider's Services -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Services by {{ $provider->name }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($provider->services as $service)
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition border border-gray-100">
                    <div class="h-40 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                        @if($service->cover_image)
                            <img src="{{ asset('storage/' . $service->cover_image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-mountain text-4xl text-white/80"></i>
                        @endif
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-800">{{ $service->name }}</h4>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-blue-600 font-bold">Rs. {{ number_format($service->price, 0) }}</span>
                            <span class="text-xs text-gray-500">{{ $service->category->name ?? '' }}</span>
                        </div>
                        <a href="{{ route('public.services.show', $service->slug) }}" 
                           class="mt-3 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Details →
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full">No services available from this provider.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection