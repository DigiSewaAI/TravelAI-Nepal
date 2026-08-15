@extends('layouts.public')

@section('title', 'My Dashboard | TravelAI Nepal')

@section('content')

{{-- ========== HERO / WELCOME SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-10 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold">
                    Good {{ $greeting ?? 'Morning' }}, {{ Auth::user()->name ?? 'Traveler' }} 👋
                </h1>
                <p class="text-blue-100 text-lg mt-1">Ready for your next Nepal adventure?</p>
            </div>
            <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
                <a href="{{ route('home') }}#ai-planner" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-robot"></i> Plan with AI
                </a>
                <a href="{{ route('public.services.index') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-compass"></i> Explore Nepal
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- ========== STATS CARDS ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600">{{ $bookingStats['upcoming'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Upcoming</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-green-600">{{ $bookingStats['active'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Active</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-gray-800">{{ $bookingStats['completed'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Completed</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-purple-600">{{ $reviews->count() }}</p>
            <p class="text-xs text-gray-500">Reviews</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ========== LEFT COLUMN: Active Trip + Bookings ========== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Active Trip --}}
            @if($activeTrip)
                <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-hiking text-blue-600"></i> My Active Trip
                    </h3>
                    <div class="mt-3">
                        <h4 class="text-xl font-semibold text-gray-900">{{ $activeTrip->service->name ?? 'N/A' }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-1"></i> 
                            {{ $activeTrip->start_date ? $activeTrip->start_date->format('M d, Y') : 'TBD' }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-circle text-[6px] mr-1 align-middle"></i> Active
                            </span>
                            <span class="text-sm text-gray-500">
                                Status: <span class="font-medium text-gray-700">{{ ucfirst($activeTrip->status) }}</span>
                            </span>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium ml-auto">
                                View Trek Passport <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border p-6 text-center">
                    <i class="fas fa-hiking text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-700">No Active Trip</h3>
                    <p class="text-sm text-gray-400">Plan your next adventure with AI</p>
                    <a href="{{ route('home') }}#ai-planner" class="inline-block mt-3 text-blue-600 hover:underline text-sm">Start Planning →</a>
                </div>
            @endif

            {{-- My Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-calendar-check text-blue-600"></i> My Bookings
                    </h3>
                    <span class="text-sm text-gray-400">{{ $bookings->count() }} total</span>
                </div>

                @if($bookings->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($bookings->take(5) as $booking)
                            <div class="py-3 flex flex-wrap justify-between items-center gap-2">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $booking->service->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-400">
                                        <i class="far fa-calendar-alt mr-1"></i> 
                                        {{ $booking->start_date ? $booking->start_date->format('M d, Y') : 'TBD' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    @if($booking->status === 'completed' && !$booking->review)
                                        <a href="{{ route('traveler.reviews.create', $booking) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-star"></i> Review
                                        </a>
                                    @endif
                                    @if($booking->review)
                                        <span class="text-sm text-green-600">✅ Reviewed</span>
                                    @endif
                                    <a href="{{ route('traveler.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($bookings->count() > 5)
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm">View All Bookings →</a>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 text-center py-6">No bookings yet. Start exploring!</p>
                    <div class="text-center">
                        <a href="{{ route('public.services.index') }}" class="text-blue-600 hover:underline text-sm">Explore Services →</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========== RIGHT COLUMN: Reviews + Quick Actions ========== --}}
        <div class="space-y-6">

            {{-- My Reviews --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-star text-yellow-500"></i> My Reviews
                </h3>
                @if($reviews->count() > 0)
                    <div class="space-y-3">
                        @foreach($reviews->take(3) as $review)
                            <div class="border-b pb-2 last:border-0">
                                <div class="flex justify-between items-start">
                                    <span class="font-medium text-sm text-gray-800">{{ $review->service->name ?? 'N/A' }}</span>
                                    <span class="text-yellow-500 text-sm">{{ str_repeat('⭐', $review->rating) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $review->comment ?: 'No comment' }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if($reviews->count() > 3)
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm">View All Reviews →</a>
                        </div>
                    @endif
                @else
                    <p class="text-gray-400 text-sm text-center py-4">No reviews yet.</p>
                @endif
            </div>

            {{-- My Trek History (QR Check-ins) --}}
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
        <i class="fas fa-route text-green-600"></i> My Trek History
    </h3>
    @if($qrScans->count() > 0)
        <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
            @foreach($qrScans as $scan)
                <div class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0">
                    <div>
                        <p class="font-medium text-sm text-gray-800">{{ $scan->booking->service->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-400">
                            <i class="fas fa-map-pin mr-1 text-blue-500"></i> 
                            {{ $scan->checkpoint_name ?? 'Check-in' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-medium text-green-600">
                            <i class="fas fa-check-circle mr-1"></i> Checked In
                        </span>
                        <p class="text-[10px] text-gray-400">{{ $scan->scanned_at ? $scan->scanned_at->format('M d, Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        @if($qrScans->count() > 10)
            <div class="mt-3 text-center">
                <a href="#" class="text-blue-600 hover:underline text-sm">View All History →</a>
            </div>
        @endif
    @else
        <div class="text-center py-4">
            <i class="fas fa-route text-3xl text-gray-300 mb-2"></i>
            <p class="text-gray-400 text-sm">No trek history yet.</p>
            <p class="text-xs text-gray-400">Check-in at a trek checkpoint to start your journey.</p>
        </div>
    @endif
</div>

            {{-- 🔥 AI Travel Planner (Prominent Card) --}}
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">AI Travel Planner</h3>
                        <p class="text-blue-100 text-sm mt-1 max-w-md">
                            Tell us your budget, travel days, interests and travel style — we'll build a personalized itinerary for you.
                        </p>
                        <a href="{{ route('home') }}#ai-planner" class="inline-block mt-4 bg-white text-blue-600 hover:bg-gray-100 px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-md hover:shadow-lg">
                            Create AI Itinerary <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 🔥 Digital Trek Passport (Premium Coming Soon) --}}
            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-passport text-blue-500 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Digital Trek Passport</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Your digital record for future treks, check-ins and verified journey history.</p>
                        <span class="inline-block mt-2 text-[10px] bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full font-medium">Coming 2026</span>
                    </div>
                </div>
            </div>

            {{-- 🔥 Safety Center (Premium Coming Soon) --}}
            <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-shield-alt text-red-500 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800">Safety Center</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Safety tools and trek support designed for Nepal's mountain routes.</p>
                        <span class="inline-block mt-2 text-[10px] bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full font-medium">Coming 2026</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection