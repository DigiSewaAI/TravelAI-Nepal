@extends('layouts.public')

@section('title', $provider->name . ' | TravelAI Nepal')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.providers.index') }}" class="hover:text-blue-600">Providers</a>
        <span class="mx-2">/</span>
        <span>{{ $provider->name }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-md border p-6 mb-6">
        <div class="flex items-center gap-4">
            @if($provider->logo_url)
                <img src="{{ asset('storage/' . $provider->logo_url) }}" 
                     class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-3xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $provider->name }}</h1>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($provider->types as $type)
                        <span class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                            {{ $type->name }}
                        </span>
                    @endforeach
                    @if($provider->verification_status === 'verified')
                        <span class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full">
                            <i class="fas fa-check-circle"></i> Verified
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if($provider->description)
            <div class="mt-4 border-t pt-4">
                <p class="text-gray-600">{{ $provider->description }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 border-t pt-4 text-sm">
            <div>
                <p class="text-gray-500">Contact Email</p>
                <p class="font-medium">{{ $provider->contact_email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-medium">{{ $provider->contact_phone ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Address</p>
                <p class="font-medium">{{ $provider->address ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Services</p>
                <p class="font-medium">{{ $provider->services()->where('status', 'active')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Services -->
    @if($provider->services->count() > 0)
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Services by {{ $provider->name }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($provider->services as $service)
                <div class="bg-white rounded-xl shadow-md border hover:shadow-lg transition overflow-hidden">
                    @if($service->cover_image)
                        <img src="{{ asset('storage/' . $service->cover_image) }}" 
                             class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                            <i class="fas fa-mountain text-4xl text-white/80"></i>
                        </div>
                    @endif
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-800">{{ $service->name }}</h4>
                        <p class="text-sm text-gray-500 line-clamp-2">{{ $service->description ?? '' }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-blue-600 font-bold">Rs. {{ number_format($service->price, 0) }}</span>
                            <a href="{{ route('public.services.show', $service->slug) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">View →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection