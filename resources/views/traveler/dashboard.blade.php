@extends('layouts.public')

@section('title', __('messages.traveler_dashboard_title'))

@section('content')

{{-- ========== HERO / WELCOME SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-10 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold">
                    {{ __('messages.traveler_greeting', ['greeting' => $greeting ?? 'Morning', 'name' => Auth::user()->name ?? 'Traveler']) }}
                </h1>
                <p class="text-blue-100 text-lg mt-1">{{ __('messages.traveler_ready_for_adventure') }}</p>
            </div>
            <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
                <a href="{{ route('home') }}#ai-planner" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-robot"></i> {{ __('messages.plan_with_ai') }}
                </a>
                <a href="{{ route('public.services.index') }}" class="bg-white text-blue-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-compass"></i> {{ __('messages.explore_nepal_btn') }}
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
            <p class="text-xs text-gray-500">{{ __('messages.traveler_stat_upcoming') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-green-600">{{ $bookingStats['active'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">{{ __('messages.traveler_stat_active') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-gray-800">{{ $bookingStats['completed'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">{{ __('messages.traveler_stat_completed') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-purple-600">{{ $reviews->count() }}</p>
            <p class="text-xs text-gray-500">{{ __('messages.traveler_stat_reviews') }}</p>
        </div>
    </div>

    {{-- ✅ PASSPORT QUICK ACCESS CARD WITH SHARE TOGGLE --}}
<div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 rounded-2xl p-6 mb-6 text-white shadow-xl relative overflow-hidden group">
    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="flex items-center gap-4">
            <div class="text-4xl">🎒</div>
            <div>
                <h3 class="text-xl font-bold">{{ __('messages.passport_card_title') }}</h3>
                <p class="text-blue-100 text-sm">
                    {{ Auth::user()->passport_privacy === 'public' ? __('messages.passport_card_public') : __('messages.passport_card_private') }}
                </p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3 flex-wrap">
            {{-- Share Toggle --}}
            <form action="{{ route('traveler.passport.toggle') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                            {{ Auth::user()->passport_privacy === 'public'
                                ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                : 'bg-white/20 hover:bg-white/30 text-white' }}">
                    {{ Auth::user()->passport_privacy === 'public' ? __('messages.passport_card_make_private') : __('messages.passport_card_share_public') }}
                </button>
            </form>
            <a href="{{ route('traveler.passport') }}"
               class="bg-white text-blue-600 px-6 py-2.5 rounded-xl font-bold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl flex items-center gap-2 group-hover:scale-105 transform duration-200">
                {{ __('messages.passport_card_view_full') }}
            </a>
        </div>
    </div>
    @if(Auth::user()->passport_privacy === 'public')
        <div class="relative z-10 mt-3 text-sm text-blue-100">
            {{ __('messages.passport_card_share_label') }}
            <span class="font-mono text-xs bg-black/20 px-2 py-1 rounded">{{ url('/passport/' . Auth::user()->passport_public_id) }}</span>
            <button onclick="navigator.clipboard?.writeText('{{ url('/passport/' . Auth::user()->passport_public_id) }}')"
                    class="ml-2 text-xs bg-white/20 px-2 py-1 rounded hover:bg-white/30 transition">
                {{ __('messages.passport_card_copy') }}
            </button>
        </div>
    @endif
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ========== LEFT COLUMN: Active Trip + Bookings ========== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Active Trip --}}
            @if($activeTrip)
                <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-hiking text-blue-600"></i> {{ __('messages.traveler_active_trip_title') }}
                    </h3>
                    <div class="mt-3">
                        <h4 class="text-xl font-semibold text-gray-900">{{ $activeTrip->service->name ?? __('messages.na') }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-1"></i> 
                            {{ $activeTrip->start_date ? $activeTrip->start_date->format('M d, Y') : __('messages.tbd') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-circle text-[6px] mr-1 align-middle"></i> {{ __('messages.traveler_active') }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ __('messages.traveler_status_label') }}: <span class="font-medium text-gray-700">{{ ucfirst($activeTrip->status) }}</span>
                            </span>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium ml-auto">
                                {{ __('messages.traveler_view_trek_passport') }} <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border p-6 text-center">
                    <i class="fas fa-hiking text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-700">{{ __('messages.traveler_no_active_trip') }}</h3>
                    <p class="text-sm text-gray-400">{{ __('messages.traveler_no_active_trip_sub') }}</p>
                    <a href="{{ route('home') }}#ai-planner" class="inline-block mt-3 text-blue-600 hover:underline text-sm">{{ __('messages.traveler_start_planning') }} →</a>
                </div>
            @endif

            {{-- My Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-calendar-check text-blue-600"></i> {{ __('messages.traveler_my_bookings') }}
                    </h3>
                    <span class="text-sm text-gray-400">{{ $bookings->count() }} {{ __('messages.traveler_total') }}</span>
                </div>

                @if($bookings->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($bookings->take(5) as $booking)
                            <div class="py-3 flex flex-wrap justify-between items-center gap-2">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $booking->service->name ?? __('messages.na') }}</p>
                                    <p class="text-xs text-gray-400">
                                        <i class="far fa-calendar-alt mr-1"></i> 
                                        {{ $booking->start_date ? $booking->start_date->format('M d, Y') : __('messages.tbd') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        @if($booking->status === 'pending') {{ __('messages.pending') }}
                                        @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                                        @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                                        @else {{ __('messages.cancelled') }} @endif
                                    </span>
                                    @if($booking->status === 'completed' && !$booking->review)
                                        <a href="{{ route('traveler.reviews.create', $booking) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-star"></i> {{ __('messages.traveler_write_review') }}
                                        </a>
                                    @endif
                                    @if($booking->review)
                                        <span class="text-sm text-green-600">✅ {{ __('messages.traveler_reviewed') }}</span>
                                    @endif
                                    <a href="{{ route('traveler.bookings.show', $booking->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        {{ __('messages.view') }} <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($bookings->count() > 5)
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm">{{ __('messages.traveler_view_all_bookings') }} →</a>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500 text-center py-6">{{ __('messages.traveler_no_bookings_yet') }}</p>
                    <div class="text-center">
                        <a href="{{ route('public.services.index') }}" class="text-blue-600 hover:underline text-sm">{{ __('messages.traveler_explore_services') }} →</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========== RIGHT COLUMN: Reviews + Quick Actions ========== --}}
        <div class="space-y-6">

            {{-- My Reviews --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-star text-yellow-500"></i> {{ __('messages.traveler_my_reviews') }}
                </h3>
                @if($reviews->count() > 0)
                    <div class="space-y-3">
                        @foreach($reviews->take(3) as $review)
                            <div class="border-b pb-2 last:border-0">
                                <div class="flex justify-between items-start">
                                    <span class="font-medium text-sm text-gray-800">{{ $review->service->name ?? __('messages.na') }}</span>
                                    <span class="text-yellow-500 text-sm">{{ str_repeat('⭐', $review->rating) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $review->comment ?: __('messages.traveler_no_comment') }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if($reviews->count() > 3)
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm">{{ __('messages.traveler_view_all_reviews') }} →</a>
                        </div>
                    @endif
                @else
                    <p class="text-gray-400 text-sm text-center py-4">{{ __('messages.traveler_no_reviews_yet') }}</p>
                @endif
            </div>

            {{-- My Trek History (QR Check-ins) --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-route text-green-600"></i> {{ __('messages.traveler_trek_history') }}
                </h3>
                @if($qrScans->count() > 0)
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                        @foreach($qrScans as $scan)
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0">
                                <div>
                                    <p class="font-medium text-sm text-gray-800">{{ $scan->booking->service->name ?? __('messages.na') }}</p>
                                    <p class="text-xs text-gray-400">
                                        <i class="fas fa-map-pin mr-1 text-blue-500"></i> 
                                        {{ $scan->checkpoint_name ?? __('messages.traveler_checkin_default') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-medium text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i> {{ __('messages.traveler_checked_in') }}
                                    </span>
                                    <p class="text-[10px] text-gray-400">{{ $scan->scanned_at ? $scan->scanned_at->format('M d, Y H:i') : __('messages.na') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($qrScans->count() > 10)
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm">{{ __('messages.traveler_view_all_history') }} →</a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-route text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-400 text-sm">{{ __('messages.traveler_no_trek_history') }}</p>
                        <p class="text-xs text-gray-400">{{ __('messages.traveler_no_trek_history_sub') }}</p>
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
                        <h3 class="text-xl font-bold">{{ __('messages.traveler_ai_planner_card_title') }}</h3>
                        <p class="text-blue-100 text-sm mt-1 max-w-md">
                            {{ __('messages.traveler_ai_planner_card_desc') }}
                        </p>
                        <a href="{{ route('home') }}#ai-planner" class="inline-block mt-4 bg-white text-blue-600 hover:bg-gray-100 px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-md hover:shadow-lg">
                            {{ __('messages.traveler_create_ai_itinerary') }} <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 📸 My Travel Memories --}}
<div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-images text-green-500 text-xl"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-gray-800">📸 My Travel Memories</h4>
            <p class="text-xs text-gray-500 mt-0.5">Upload photos & videos from your journey checkpoints.</p>
            
            {{-- ✅ Upload Form --}}
            <form id="uploadForm" class="mt-3" enctype="multipart/form-data">
                @csrf
                <div class="flex flex-wrap gap-2">
                    <select name="checkpoint" id="checkpointSelect" class="flex-1 min-w-[150px] text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Select a checkpoint...</option>
                        @foreach($userWaypoints as $wp)
                            <option value="{{ $wp->name }}">{{ $wp->name }}</option>
                        @endforeach
                    </select>
                    <input type="file" name="media" id="fileInput" accept="image/*,video/*" class="flex-1 min-w-[150px] text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700" required>
                    <button type="submit" id="uploadBtn" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
                <div id="uploadMessage" class="mt-2 text-sm hidden"></div>
            </form>

            {{-- Session messages --}}
            @if(session('upload_success'))
                <div class="mt-2 text-sm text-green-600">{{ session('upload_success') }}</div>
            @endif
            @if(session('upload_error'))
                <div class="mt-2 text-sm text-red-600">{{ session('upload_error') }}</div>
            @endif

            {{-- Existing Memories --}}
            @if($userMedia->count() > 0)
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach($userMedia->take(6) as $media)
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square bg-gray-100">
                            @if($media->media_type === 'image')
                                <img src="{{ asset('storage/' . $media->optimized_path) }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover">
                            @endif
                            <form action="{{ route('traveler.memory.delete') }}" method="POST" class="absolute top-1 right-1">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $media->id }}">
                                <button type="submit" onclick="return confirm('Delete this memory?')" class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                @if($userMedia->count() > 6)
                    <p class="text-xs text-gray-400 mt-2">+{{ $userMedia->count() - 6 }} more</p>
                @endif
            @else
                <p class="text-xs text-gray-400 mt-3">No memories uploaded yet.</p>
            @endif
        </div>
    </div>
</div>

            {{-- 🎬 My Journey Replay (Always Visible) --}}
<div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-5 text-white shadow-lg hover:shadow-xl transition group">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <i class="fas fa-film text-2xl"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-lg">🎬 My Journey Replay</h4>
            <p class="text-sm text-purple-100 mt-0.5">Turn your TravelAI Nepal experiences into a beautiful travel memory.</p>
            <a href="{{ route('traveler.journey-replay') }}" 
               class="inline-block mt-2 bg-white text-purple-600 hover:bg-gray-100 px-4 py-1.5 rounded-lg text-sm font-semibold transition shadow group-hover:scale-105 transform duration-200">
                Relive Your Journey →
            </a>
        </div>
    </div>
</div>

            {{-- 🔥 Safety Center --}}
<div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shield-alt text-red-500 text-xl"></i>
        </div>
        <div class="flex-1">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold text-gray-800">{{ __('messages.traveler_safety_center_title') }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.traveler_safety_center_desc') }}</p>
                </div>
                @if(isset($unreadAlerts) && count($unreadAlerts) > 0)
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ count($unreadAlerts) }}</span>
                @endif
            </div>

            {{-- Alerts List --}}
            @if(isset($unreadAlerts) && count($unreadAlerts) > 0)
                <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                    @foreach($unreadAlerts as $alert)
                        <div class="border-l-4 
                            @if($alert['severity'] === 'critical') border-red-600
                            @elseif($alert['severity'] === 'high') border-orange-500
                            @elseif($alert['severity'] === 'moderate') border-yellow-500
                            @else border-green-500 @endif
                            bg-gray-50 p-3 rounded-r-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-sm">
                                        @if($alert['severity'] === 'critical') 🔴
                                        @elseif($alert['severity'] === 'high') 🟠
                                        @elseif($alert['severity'] === 'moderate') 🟡
                                        @else 🟢 @endif
                                        {{ $alert['incident']['title'] ?? $alert['message'] ?? 'Safety Alert' }}
                                    </p>
                                    <p class="text-xs text-gray-600">{{ $alert['message'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
    {{ isset($alert['sent_at']) ? \Carbon\Carbon::parse($alert['sent_at'])->diffForHumans() : 'Just now' }}
</p>
                                </div>
                                <form method="POST" action="{{ route('traveler.alert.read', $alert['id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">Mark as Read</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-3">
                    <p class="text-gray-500 text-sm">✅ No safety alerts at this time.</p>
                </div>
            @endif

            <div class="mt-3">
                <a href="{{ route('safety.index') }}" class="text-sm text-blue-600 hover:underline">
                    View Safety Map →
                </a>
            </div>
        </div>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const btn = document.getElementById('uploadBtn');
    const msg = document.getElementById('uploadMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        msg.classList.add('hidden');

        fetch('{{ route("traveler.checkpoint.upload") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('✅ ' + data.message, 'green');
                setTimeout(() => location.reload(), 1000);
            } else {
                showMessage('❌ ' + data.message, 'red');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i> Upload';
            }
        })
        .catch(error => {
            showMessage('❌ Network error', 'red');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Upload';
        });
    });

    function showMessage(text, color) {
        msg.textContent = text;
        msg.className = 'mt-2 text-sm ' + (color === 'red' ? 'text-red-600' : 'text-green-600');
        msg.classList.remove('hidden');
    }
});
</script>

@endsection