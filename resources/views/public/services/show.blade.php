@extends('layouts.public')

@section('title', $service->name . ' | TravelAI Nepal')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">Explore</a>
        <span class="mx-2">/</span>
        <span>{{ $service->name }}</span>
    </nav>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Gallery/Images -->
        <div>
            @if($service->cover_image)
                <img src="{{ asset('storage/' . $service->cover_image) }}" 
                     class="w-full rounded-xl shadow-lg object-cover h-96">
            @else
                <div class="w-full h-96 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-mountain text-6xl text-white/80"></i>
                </div>
            @endif
            
            @if($service->gallery && count($service->gallery) > 0)
                <div class="grid grid-cols-4 gap-2 mt-2">
                    @foreach(array_slice($service->gallery, 0, 4) as $image)
                        <img src="{{ asset('storage/' . $image) }}" 
                             class="w-full h-20 object-cover rounded-lg cursor-pointer hover:opacity-75">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Service Details -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $service->name }}</h1>
            
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                    {{ $service->category->name ?? 'N/A' }}
                </span>
                @if($service->provider->verification_status === 'verified')
                    <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-700">
                        <i class="fas fa-check-circle"></i> Verified Provider
                    </span>
                @endif
            </div>

            <div class="mt-4">
                <p class="text-3xl font-bold text-blue-600">
                    Rs. {{ number_format($service->price, 0) }}
                    <span class="text-sm font-normal text-gray-500">/ {{ $service->trekDetail->duration_days ?? '1' }} day(s)</span>
                </p>
            </div>

            <!-- Trek Details -->
            @if($service->trekDetail)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700">Trek Details</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div><span class="text-gray-500">Duration:</span> {{ $service->trekDetail->duration_days }} days</div>
                        <div><span class="text-gray-500">Difficulty:</span> {{ ucfirst($service->trekDetail->difficulty) }}</div>
                        @if($service->trekDetail->max_altitude)
                            <div><span class="text-gray-500">Max Altitude:</span> {{ $service->trekDetail->max_altitude }}m</div>
                        @endif
                        @if($service->trekDetail->season)
                            <div><span class="text-gray-500">Season:</span> {{ $service->trekDetail->season }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tour Details -->
            @if($service->tourDetail)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700">Tour Details</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div><span class="text-gray-500">Duration:</span> {{ $service->tourDetail->duration_days }} days</div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($service->description)
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-700">Description</h3>
                    <p class="text-gray-600 mt-2">{{ $service->description }}</p>
                </div>
            @endif

            <!-- Provider Info -->
            <div class="mt-6 p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-700">Provider</h3>
                <div class="flex items-center gap-3 mt-2">
                    @if($service->provider->logo_url)
                        <img src="{{ asset('storage/' . $service->provider->logo_url) }}" 
                             class="w-12 h-12 rounded-full object-cover">
                    @else
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-building text-blue-600"></i>
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('public.providers.show', $service->provider->slug) }}" 
                           class="font-medium text-gray-800 hover:text-blue-600">
                            {{ $service->provider->name }}
                        </a>
                        <p class="text-sm text-gray-500">{{ $service->provider->contact_email ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking Button -->
            <div class="mt-6">
                <a href="{{ route('public.services.book', $service->slug) }}" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition shadow-lg hover:shadow-xl text-center block">
                    <i class="fas fa-calendar-check mr-2"></i> Book This Service
                </a>
            </div>
        </div>
    </div>

    <!-- Related Services -->
    @if($relatedServices && $relatedServices->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($relatedServices as $related)
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="h-32 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                            @if($related->cover_image)
                                <img src="{{ asset('storage/' . $related->cover_image) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-mountain text-3xl text-white/80"></i>
                            @endif
                        </div>
                        <div class="p-3">
                            <h4 class="font-semibold text-gray-800 text-sm">{{ $related->name }}</h4>
                            <p class="text-blue-600 font-bold text-sm">Rs. {{ number_format($related->price, 0) }}</p>
                            <a href="{{ route('public.services.show', $related->slug) }}" 
                               class="text-xs text-blue-600 hover:text-blue-800">View →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection