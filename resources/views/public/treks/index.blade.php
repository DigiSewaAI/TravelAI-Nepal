@extends('layouts.public')

@section('title', 'All Treks, Tours & Hotels | TravelAI Nepal')

@section('content')
<style>
    .trek-card:hover { transform: translateY(-4px); transition: 0.2s; }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            @if($category == 'trek') Treks
            @elseif($category == 'tour') Tours
            @else Hotels
            @endif
        </h1>
        <a href="{{ url('/') }}" class="text-blue-600 hover:underline">← Back to Home</a>
    </div>

    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 mb-6">
        <a href="{{ request()->fullUrlWithQuery(['category' => 'trek', 'page' => 1]) }}" 
           class="py-2 px-4 text-sm font-medium {{ $category == 'trek' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            🏔️ Treks
        </a>
        <a href="{{ request()->fullUrlWithQuery(['category' => 'tour', 'page' => 1]) }}" 
           class="py-2 px-4 text-sm font-medium {{ $category == 'tour' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            🚐 Tours
        </a>
        <a href="{{ request()->fullUrlWithQuery(['category' => 'hotel', 'page' => 1]) }}" 
           class="py-2 px-4 text-sm font-medium {{ $category == 'hotel' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            🏨 Hotels
        </a>
    </div>

    <!-- Search & Filter Form -->
    <form method="GET" action="{{ url('/treks') }}" class="mb-8 bg-white p-4 rounded-xl shadow-sm border">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Trek name..." class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                <select name="difficulty" class="w-full border rounded-lg px-3 py-2">
                    <option value="">All</option>
                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                    <option value="moderate" {{ request('difficulty') == 'moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Days</label>
                <input type="number" name="min_days" value="{{ request('min_days') }}" placeholder="e.g., 5" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Days</label>
                <input type="number" name="max_days" value="{{ request('max_days') }}" placeholder="e.g., 20" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">Filter</button>
            </div>
        </div>
        @if(request()->anyFilled(['search', 'difficulty', 'min_days', 'max_days']))
            <div class="mt-3 text-right">
                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'difficulty' => null, 'min_days' => null, 'max_days' => null]) }}" 
                   class="text-sm text-red-500 hover:underline">Clear filters</a>
            </div>
        @endif
    </form>

    <!-- Trek/Tour/Hotel Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($treks as $item)
        <div class="bg-white rounded-2xl shadow-md overflow-hidden trek-card transition duration-300 hover:shadow-xl border border-gray-100">
            @if($item->cover_image)
                <img src="{{ asset('storage/' . $item->cover_image) }}" class="h-48 w-full object-cover">
            @else
                <div class="h-48 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                    @if($category == 'hotel')
                        <i class="fas fa-hotel text-5xl text-white/80"></i>
                    @elseif($category == 'tour')
                        <i class="fas fa-bus text-5xl text-white/80"></i>
                    @else
                        <i class="fas fa-mountain text-5xl text-white/80"></i>
                    @endif
                </div>
            @endif
            <div class="p-5">
                <div class="flex justify-between items-start">
                    <h2 class="text-xl font-bold text-gray-800">{{ $item->name }}</h2>
                    <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">${{ number_format($item->price, 0) }}</span>
                </div>
                <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-500">
                    <span><i class="far fa-calendar-alt"></i> {{ $item->duration_days }} days</span>
                    <span><i class="fas fa-chart-line"></i> {{ ucfirst($item->difficulty) }}</span>
                </div>
                <p class="text-gray-600 text-sm mt-2">{{ $item->agency->name ?? 'Independent Agency' }}</p>
                <a href="{{ route('trek.show', $item) }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800 font-medium text-sm">View Details →</a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-10 text-gray-500">No {{ $category }} found matching your criteria.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $treks->links() }}
    </div>
</div>
@endsection