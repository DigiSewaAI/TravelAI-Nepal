@extends('layouts.public')

@section('title', __('messages.explore_services_title'))
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Category Tabs -->
    <div class="flex flex-wrap gap-2 mb-6 border-b pb-4">
        <a href="{{ route('public.services.index', ['category' => 'all']) }}" 
           class="px-4 py-2 rounded-lg {{ $categorySlug == 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ __('messages.all') }}
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('public.services.index', ['category' => $cat->slug]) }}" 
               class="px-4 py-2 rounded-lg {{ $categorySlug == $cat->slug ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Search & Filters -->
    <form method="GET" action="{{ route('public.services.index') }}" class="mb-6 flex flex-wrap gap-4">
        <input type="hidden" name="category" value="{{ $categorySlug }}">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="{{ __('messages.search_services_placeholder') }}" 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="min_price" value="{{ request('min_price') }}" 
                   placeholder="{{ __('messages.min_price_placeholder', ['currency' => session('display_currency', 'USD')]) }}" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="max_price" value="{{ request('max_price') }}" 
                   placeholder="{{ __('messages.max_price_placeholder', ['currency' => session('display_currency', 'USD')]) }}" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="min_days" value="{{ request('min_days') }}" 
                   placeholder="{{ __('messages.min_days_placeholder') }}" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="max_days" value="{{ request('max_days') }}" 
                   placeholder="{{ __('messages.max_days_placeholder') }}" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <select name="difficulty" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('messages.difficulty') }}</option>
                <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>{{ __('messages.easy') }}</option>
                <option value="moderate" {{ request('difficulty') == 'moderate' ? 'selected' : '' }}>{{ __('messages.moderate') }}</option>
                <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>{{ __('messages.hard') }}</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-search"></i> {{ __('messages.filter_btn') }}
        </button>
        <a href="{{ route('public.services.index', ['category' => $categorySlug]) }}" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
            {{ __('messages.reset') }}
        </a>
    </form>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition border border-gray-100">
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center overflow-hidden">
                @if($service->cover_image)
                    <img src="{{ asset('storage/' . $service->cover_image) }}" 
                         alt="{{ $service->name }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="TravelAI Nepal"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @endif
            </div>
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-gray-800">{{ $service->name }}</h3>
                    <div class="text-right">
                        @php
                            $currencyService = app(\App\Services\CurrencyService::class);
                            $displayCurrency = $currencyService->getDisplayCurrency();
                            $baseCurrency = $service->currency ?? 'USD';
                            $displayPrice = $currencyService->convert($service->price, $baseCurrency, $displayCurrency);
                            $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
                        @endphp
                        <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full block">
                            {{ $formattedPrice }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-500">
                    <span><i class="far fa-calendar-alt"></i> {{ $service->trekDetail->duration_days ?? __('messages.na') }} {{ __('messages.days') }}</span>
                    @if($service->trekDetail)
                        <span><i class="fas fa-chart-line"></i> {{ ucfirst($service->trekDetail->difficulty) }}</span>
                    @endif
                    <span><i class="fas fa-tag"></i> {{ $service->category->name ?? __('messages.na') }}</span>
                </div>
                <p class="text-gray-600 text-sm mt-3">{{ $service->provider->name ?? 'TravelAI Partner' }}</p>
                <a href="{{ route('public.services.show', $service->slug) }}" 
                   class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium text-sm">
                    {{ __('messages.view_details') }} →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-10 text-gray-500">
            <i class="fas fa-search text-4xl mb-4"></i>
            <p class="text-lg">{{ __('messages.no_services_found') }}</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $services->links() }}
    </div>
</div>
@endsection