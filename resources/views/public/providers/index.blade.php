@extends('layouts.public')

@section('title', __('messages.providers_page_title'))

@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">🇳🇵 {{ __('messages.providers_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.providers_hero_subtitle') }}
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
            <p class="text-2xl font-bold text-blue-600">{{ $providers->total() }}</p>
            <p class="text-sm text-gray-600">{{ __('messages.total_providers') }}</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100">
            <p class="text-2xl font-bold text-green-600">{{ $providers->where('verification_status', 'verified')->count() }}</p>
            <p class="text-sm text-gray-600">{{ __('messages.verified') }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 text-center border border-purple-100">
            <p class="text-2xl font-bold text-purple-600">{{ $providerTypes->count() }}</p>
            <p class="text-sm text-gray-600">{{ __('messages.categories') }}</p>
        </div>
        <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100">
            <p class="text-2xl font-bold text-yellow-600">{{ $providers->sum(fn($p) => $p->services()->where('status', 'active')->count()) }}</p>
            <p class="text-sm text-gray-600">{{ __('messages.total_services') }}</p>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                <input type="text" name="search" placeholder="{{ __('messages.search_providers_placeholder') }}" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.category') }}</label>
                <select name="type" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">{{ __('messages.all_types') }}</option>
                    @foreach($providerTypes as $type)
                        <option value="{{ $type->slug }}" {{ request('type') == $type->slug ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.sort_by') }}</label>
                <select name="sort" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('messages.latest') }}</option>
                    <option value="most_services" {{ request('sort') == 'most_services' ? 'selected' : '' }}>{{ __('messages.most_services') }}</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>{{ __('messages.name_az') }}</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-1"></i> {{ __('messages.filter_btn') }}
                </button>
                <a href="{{ route('public.providers.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                    {{ __('messages.reset') }}
                </a>
            </div>
        </form>
    </div>

    {{-- Providers Grid --}}
    @if($providers->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($providers as $provider)
                <div class="group bg-white rounded-xl shadow-md border hover:shadow-xl transition-all duration-300 overflow-hidden hover:border-blue-300">
    
    {{-- ========== COVER IMAGE SECTION ========== --}}
<div class="relative h-48 bg-gradient-to-r from-blue-500 to-indigo-600 overflow-hidden">
    @if($provider->cover_image)
        <img src="{{ asset('storage/' . $provider->cover_image) }}" 
             alt="{{ $provider->name }} cover"
             class="w-full h-full object-cover object-left-center group-hover:scale-105 transition-transform duration-300"
             onerror="this.onerror=null; this.style.display='none';">
    @else
        <img src="{{ asset('images/logo.png') }}" 
             alt="TravelAI Nepal"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
    @endif

    {{-- Gradient Overlay --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

    {{-- LOGO + NAME + BADGES (bottom left) --}}
    <div class="absolute bottom-0 left-0 right-0 p-4 flex items-end gap-3">
        @if($provider->logo_url)
            <img src="{{ asset('storage/' . $provider->logo_url) }}" 
                 alt="{{ $provider->name }} logo"
                 class="w-16 h-16 rounded-full border-2 border-white shadow-lg object-cover bg-white flex-shrink-0"
                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">
        @else
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Default logo"
                 class="w-16 h-16 rounded-full border-2 border-white shadow-lg object-cover bg-white flex-shrink-0">
        @endif

        <div class="flex-1 min-w-0">
            <h3 class="font-bold text-white drop-shadow-lg truncate text-shadow">{{ $provider->name }}</h3>
            <div class="flex flex-wrap gap-1 mt-1">
                @foreach($provider->types as $type)
                    <span class="text-xs bg-white/20 backdrop-blur-sm text-white px-2 py-0.5 rounded-full">
                        {{ $type->name }}
                    </span>
                @endforeach
                @if($provider->verification_status === 'verified')
                    <span class="text-xs bg-green-500/80 text-white px-2 py-0.5 rounded-full">
                        <i class="fas fa-check-circle"></i> {{ __('messages.verified') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

    {{-- ========== CARD BODY ========== --}}
    <div class="p-4">
        <p class="text-sm text-gray-600 line-clamp-2">
            {{ $provider->description ?? __('messages.no_description_available') }}
        </p>

        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>
                    <i class="fas fa-briefcase mr-1"></i> 
                    {{ $provider->services()->where('status', 'active')->count() }} {{ __('messages.services') }}
                </span>
                @php
                    $rating = $provider->services()->with('reviews')->get()->flatMap->reviews->avg('rating');
                @endphp
                @if($rating)
                    <span>
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        {{ number_format($rating, 1) }}
                    </span>
                @endif
            </div>
            <a href="{{ route('public.providers.show', $provider->slug ?? $provider->id) }}" 
               class="text-blue-600 hover:text-blue-800 text-sm font-medium group-hover:underline">
                {{ __('messages.view_profile') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            {{ $providers->appends(request()->query())->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-16 bg-white rounded-xl border">
            <i class="fas fa-building text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600">{{ __('messages.no_providers_found') }}</h3>
            <p class="text-gray-400 mt-2">{{ __('messages.try_adjusting_filters') }}</p>
            <a href="{{ route('public.providers.index') }}" class="inline-block mt-4 text-blue-600 hover:underline">
                {{ __('messages.clear_all_filters') }}
            </a>
        </div>
    @endif
</div>
@endsection