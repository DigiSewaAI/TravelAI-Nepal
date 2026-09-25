@extends('layouts.public')

@section('title', $category->name . ' in Nepal — Verified Providers | TravelAI Nepal')
@section('meta_description', 'Browse verified ' . $category->name . ' services across Nepal. Real prices from ' . $services->total() . '+ listings. Trusted providers, instant booking, no hidden fees.')
@section('meta_keywords', $category->name . ' Nepal, ' . strtolower($category->name) . ' services Nepal, book ' . strtolower($category->name) . ' Nepal, verified ' . strtolower($category->name))

@push('head')
{{-- ========== JSON-LD: CollectionPage ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "CollectionPage",
  "name": "{{ addslashes($category->name) }} in Nepal",
  "description": "Browse verified {{ addslashes($category->name) }} services across Nepal.",
  "url": "{{ url()->current() }}"
}
</script>

{{-- ========== JSON-LD: ItemList ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "ItemList",
  "itemListElement": [
    @foreach($services->take(20) as $i => $s)
    {
      "@@type": "ListItem",
      "position": {{ $i + 1 }},
      "url": "{{ route('public.services.show', $s->slug) }}",
      "name": "{{ addslashes($s->name) }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>

{{-- ========== JSON-LD: BreadcrumbList ========== --}}
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@@type": "ListItem",
      "position": 1,
      "name": "Home",
      "item": "{{ url('/') }}"
    },
    {
      "@@type": "ListItem",
      "position": 2,
      "name": "Explore",
      "item": "{{ url('/explore') }}"
    },
    {
      "@@type": "ListItem",
      "position": 3,
      "name": "{{ addslashes($category->name) }}",
      "item": "{{ url()->current() }}"
    }
  ]
}
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- ========== BREADCRUMB ========== --}}
    <nav class="text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-blue-600">{{ __('messages.home') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">{{ __('messages.explore') }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-800 font-medium">{{ $category->name }}</span>
    </nav>

    {{-- ========== HERO ========== --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-2xl py-10 px-6 mb-8">
        <h1 class="text-3xl md:text-4xl font-bold">{{ $category->name }} in Nepal</h1>
        <p class="text-blue-100 mt-2">{{ $services->total() }}+ verified listings — real prices, trusted providers, instant booking.</p>
    </div>

    {{-- ========== CATEGORY TABS ========== --}}
    <div class="flex flex-wrap gap-2 mb-6 border-b pb-4">
        <a href="{{ route('public.services.index', ['category' => 'all']) }}"
           class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
            {{ __('messages.all') }}
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('public.services.category', $cat->slug) }}"
               class="px-4 py-2 rounded-lg {{ $category->id === $cat->id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    {{-- ========== SEARCH & FILTERS ========== --}}
    <form method="GET" action="{{ route('public.services.category', $category->slug) }}" class="mb-6 flex flex-wrap gap-4">
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
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-search"></i> {{ __('messages.filter_btn') }}
        </button>
        <a href="{{ route('public.services.category', $category->slug) }}"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
            {{ __('messages.reset') }}
        </a>
    </form>

    {{-- ========== SERVICES GRID ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition border border-gray-100">
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center overflow-hidden">
                @if($service->cover_image)
                    <img src="{{ asset('storage/' . $service->cover_image) }}"
                         alt="{{ $service->name }}"
                         loading="lazy"
                         class="w-full h-full object-cover">
                @else
                    <img src="{{ asset('images/logo.png') }}"
                         alt="TravelAI Nepal"
                         loading="lazy"
                         class="w-full h-full object-cover">
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
                            $displayPrice = $service->price !== null ? $currencyService->convert((float) $service->price, $baseCurrency, $displayCurrency) : null;
                            $formattedPrice = $displayPrice !== null ? $currencyService->format($displayPrice, $displayCurrency) : __('messages.na');
                        @endphp
                        <span class="text-sm font-semibold {{ $displayPrice !== null ? 'text-blue-600 bg-blue-50' : 'text-gray-400 bg-gray-50 italic' }} px-2 py-1 rounded-full block">
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

    {{-- ========== PAGINATION ========== --}}
    <div class="mt-8">
        {{ $services->appends(request()->query())->links() }}
    </div>
</div>
@endsection