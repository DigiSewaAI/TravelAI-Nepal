@extends('layouts.public')

@section('title', $service->name . ' | TravelAI Nepal')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">{{ __('messages.home') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">{{ __('messages.explore') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $service->name }}</span>
    </nav>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Gallery/Images -->
        <div>
            @if($service->cover_image)
                <img src="{{ asset('storage/' . $service->cover_image) }}" 
                     alt="{{ $service->name }}"
                     class="w-full rounded-xl shadow-lg object-cover h-96">
            @else
                <div class="w-full h-96 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="TravelAI Nepal"
                         class="w-48 h-48 object-contain opacity-50">
                </div>
            @endif

            @php
                $gallery = is_array($service->gallery) ? $service->gallery : json_decode($service->gallery, true) ?? [];
            @endphp

            @if(count($gallery) > 0)
                <div class="grid grid-cols-4 gap-2 mt-2">
                    @foreach(array_slice($gallery, 0, 4) as $image)
                        <img src="{{ asset('storage/' . $image) }}" 
                             alt="{{ __('messages.gallery_image') }}"
                             class="w-full h-20 object-cover rounded-lg cursor-pointer hover:opacity-75">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Service Details -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $service->name }}</h1>
            
            <div class="flex items-center gap-2 mt-2 flex-wrap">
                <span class="px-2 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                    {{ $service->category->name ?? __('messages.na') }}
                </span>

                @if($service->averageRating() > 0)
                    <span class="flex items-center text-sm">
                        @for($i=1; $i<=5; $i++)
                            <span class="text-yellow-400 {{ $i <= floor($service->averageRating()) ? 'fas fa-star' : 'far fa-star' }}"></span>
                        @endfor
                        <span class="ml-1 text-gray-600">{{ number_format($service->averageRating(), 1) }} ({{ $service->ratingsCount() }})</span>
                    </span>
                @endif

                @if($service->provider->verification_status === 'verified')
                    <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-700">
                        <i class="fas fa-check-circle"></i> {{ __('messages.verified_provider') }}
                    </span>
                @endif
            </div>

            @php
                $currencyService = app(\App\Services\CurrencyService::class);
                $displayCurrency = $currencyService->getDisplayCurrency();
                $baseCurrency = $service->currency ?? 'USD';
                $displayPrice = $currencyService->convert($service->price, $baseCurrency, $displayCurrency);
                $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
                $showBaseNote = ($baseCurrency !== $displayCurrency);
            @endphp

            <div class="mt-4">
                <p class="text-3xl font-bold text-blue-600">
                    {{ $formattedPrice }}
                    @if($service->trekDetail)
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.pax') }}</span>
                        <span class="text-sm font-normal text-gray-400 ml-2">({{ $service->trekDetail->duration_days ?? '1' }} {{ __('messages.days') }})</span>
                    @elseif($service->hotelDetail)
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.night') }}</span>
                    @elseif($service->tourDetail)
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.person') }}</span>
                    @else
                        <span class="text-sm font-normal text-gray-500">/ {{ __('messages.person') }}</span>
                    @endif
                </p>
                @if($showBaseNote)
                    <p class="text-xs text-gray-400 mt-1">{{ __('messages.base_price') }}: {{ $currencyService->format($service->price, $baseCurrency) }}</p>
                @endif
            </div>

            <!-- Trek Details -->
            @if($service->trekDetail)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700">{{ __('messages.trek_details') }}</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div><span class="text-gray-500">{{ __('messages.duration') }}:</span> {{ $service->trekDetail->duration_days }} {{ __('messages.days') }}</div>
                        <div><span class="text-gray-500">{{ __('messages.difficulty') }}:</span> {{ ucfirst($service->trekDetail->difficulty) }}</div>
                        @if($service->trekDetail->max_altitude)
                            <div><span class="text-gray-500">{{ __('messages.max_altitude') }}:</span> {{ $service->trekDetail->max_altitude }}m</div>
                        @endif
                        @if($service->trekDetail->season)
                            <div><span class="text-gray-500">{{ __('messages.season') }}:</span> {{ $service->trekDetail->season }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tour Details -->
            @if($service->tourDetail)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-700">{{ __('messages.tour_details') }}</h3>
                    <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                        <div><span class="text-gray-500">{{ __('messages.duration') }}:</span> {{ $service->tourDetail->duration_days }} {{ __('messages.days') }}</div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($service->description)
                <div class="mt-4">
                    <h3 class="font-semibold text-gray-700">{{ __('messages.description') }}</h3>
                    <p class="text-gray-600 mt-2">{{ $service->description }}</p>
                </div>
            @endif

            <!-- Provider Info -->
            <div class="mt-6 p-4 border rounded-lg">
                <h3 class="font-semibold text-gray-700">{{ __('messages.provider') }}</h3>
                <div class="flex items-center gap-3 mt-2">
                    @if($service->provider->logo_url)
                        <img src="{{ asset('storage/' . $service->provider->logo_url) }}" 
                             alt="{{ $service->provider->name }} logo"
                             class="w-14 h-14 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-blue-600 text-xl"></i>
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('public.providers.show', $service->provider->slug ?? $service->provider->id) }}" 
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
                    <i class="fas fa-calendar-check mr-2"></i> {{ __('messages.book_this_service') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Related Services -->
    @if($relatedServices && $relatedServices->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.related_services') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($relatedServices as $related)
                    @php
                        $relBaseCurrency = $related->currency ?? 'USD';
                        $relDisplayPrice = $currencyService->convert($related->price, $relBaseCurrency, $displayCurrency);
                        $relFormatted = $currencyService->format($relDisplayPrice, $displayCurrency);
                    @endphp
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition border border-gray-100 group">
                        <div class="h-40 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center overflow-hidden">
                            @if($related->cover_image)
                                <img src="{{ asset('storage/' . $related->cover_image) }}" 
                                     alt="{{ $related->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <img src="{{ asset('images/logo.png') }}" 
                                     alt="TravelAI Nepal"
                                     class="w-20 h-20 object-contain opacity-50 group-hover:scale-105 transition-transform duration-300">
                            @endif
                        </div>
                        <div class="p-3">
                            <h4 class="font-semibold text-gray-800 text-sm truncate">{{ $related->name }}</h4>
                            <p class="text-blue-600 font-bold text-sm">{{ $relFormatted }}</p>
                            <a href="{{ route('public.services.show', $related->slug) }}" 
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium group-hover:underline">
                                {{ __('messages.view_details') }} →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection