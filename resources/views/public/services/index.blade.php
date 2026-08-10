@extends('layouts.public')

@section('title', 'Explore Nepal | TravelAI')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Category Tabs -->
    <div class="flex flex-wrap gap-2 mb-6 border-b pb-4">
        <a href="{{ route('public.services.index', ['category' => 'all']) }}" 
           class="px-4 py-2 rounded-lg {{ $categorySlug == 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            All
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
                   placeholder="Search services..." 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="min_price" value="{{ request('min_price') }}" 
                   placeholder="Min Price" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="max_price" value="{{ request('max_price') }}" 
                   placeholder="Max Price" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="min_days" value="{{ request('min_days') }}" 
                   placeholder="Min Days" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <input type="number" name="max_days" value="{{ request('max_days') }}" 
                   placeholder="Max Days" 
                   class="w-32 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <select name="difficulty" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Difficulty</option>
                <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                <option value="moderate" {{ request('difficulty') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route('public.services.index', ['category' => $categorySlug]) }}" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
            Reset
        </a>
    </form>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition border border-gray-100">
            <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                @if($service->cover_image)
                    <img src="{{ asset('storage/' . $service->cover_image) }}" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-mountain text-5xl text-white/80"></i>
                @endif
            </div>
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <h3 class="text-xl font-bold text-gray-800">{{ $service->name }}</h3>
                    <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                        Rs. {{ number_format($service->price, 0) }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-500">
                    <span><i class="far fa-calendar-alt"></i> {{ $service->trekDetail->duration_days ?? 'N/A' }} days</span>
                    @if($service->trekDetail)
                        <span><i class="fas fa-chart-line"></i> {{ ucfirst($service->trekDetail->difficulty) }}</span>
                    @endif
                    <span><i class="fas fa-tag"></i> {{ $service->category->name ?? 'N/A' }}</span>
                </div>
                <p class="text-gray-600 text-sm mt-3">{{ $service->provider->name ?? 'TravelAI Partner' }}</p>
                <a href="{{ route('public.services.show', $service->slug) }}" 
                   class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium text-sm">
                    View Details →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-10 text-gray-500">
            <i class="fas fa-search text-4xl mb-4"></i>
            <p class="text-lg">No services found matching your criteria.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $services->links() }}
    </div>
</div>
@endsection