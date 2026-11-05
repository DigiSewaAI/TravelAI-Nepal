@extends('layouts.traveler-v2')

@section('title', __('messages.traveler_dashboard_title') . ' | TravelAI Nepal')

@section('traveler-v2-content')

    {{-- Hero --}}
    @include('traveler._partials.hero', ['counts' => $counts])

    {{-- My Trips --}}
    @include('traveler._partials.my-trips', [
        'treks' => $treks,
        'allOrders' => $allOrders,
        'shopOrders' => $shopOrders,
        'rentalOrders' => $rentalOrders,
        'rfqs' => $rfqs,
        'mixedOrderIds' => $mixedOrderIds,
        'counts' => $counts,
    
        'claimedRestaurant' => $claimedRestaurant ?? collect(),
        'claimedTransport' => $claimedTransport ?? collect(),
        'claimedHotel' => $claimedHotel ?? collect(),
    ])

    {{-- Coming Soon Categories --}}
    <div style="background: #fff; border-radius: 18px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 16px;">
            🔗 {{ __('messages.coming_soon_personal_access') ?? 'Personal booking access coming soon' }}
        </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;">
            @php
                $claimableCategories = [
                    'Hotel' => ['flag' => 'claim.hotel_enabled', 'route' => 'traveler.hotel.claim.form', 'btn' => 'claim_hotel_btn', 'icon' => '🏨'],
                    'Restaurant' => ['flag' => 'claim.restaurant_enabled', 'route' => 'traveler.restaurant.claim.form', 'btn' => 'claim_restaurant_btn', 'icon' => '🍽️'],
                    'Transport' => ['flag' => 'claim.transport_enabled', 'route' => 'traveler.transport.claim.form', 'btn' => 'claim_transport_btn', 'icon' => '🚐'],
                    'ArtCraft' => ['flag' => 'claim.artcraft_enabled', 'route' => 'traveler.artcraft.claim.form', 'btn' => 'artcraft_btn', 'icon' => '🎨'],
                    'MoneyChanger' => ['flag' => null, 'route' => null, 'btn' => 'contact_provider_btn', 'icon' => '💱'],
                    'Individual / Guide' => ['flag' => null, 'route' => null, 'btn' => 'contact_provider_btn', 'icon' => '🧑‍💼'],
                ];
            @endphp
            @foreach($claimableCategories as $cat => $meta)
                @php
                    $isEnabled = $meta['flag'] ? config($meta['flag'], false) : false;
                    $routeExists = $meta['route'] && \Illuminate\Support\Facades\Route::has($meta['route']);
                    $claimedCount = match($cat) {
                        'Hotel' => ($claimedCounts['hotel'] ?? 0),
                        'Restaurant' => ($claimedCounts['restaurant'] ?? 0),
                        'Transport' => ($claimedCounts['transport'] ?? 0),
                        'ArtCraft' => ($claimedCounts['artcraft'] ?? 0),
                        default => 0,
                    };
                @endphp
                <div style="border: 1px solid {{ $claimedCount > 0 ? '#bbf7d0' : ($isEnabled && $routeExists ? '#dbeafe' : '#e5e7eb') }}; background: {{ $claimedCount > 0 ? '#f0fdf4' : ($isEnabled && $routeExists ? '#f0f9ff' : '#fff') }}; border-radius: 10px; padding: 16px; text-align: center;">
                    <div style="font-size: 28px; margin-bottom: 6px;">{{ $meta['icon'] }}</div>
                    <div style="font-weight: 600; font-size: 13px; color: #1e293b;">{{ $cat }}</div>
                    @if($claimedCount > 0)
                        <div style="display: inline-block; margin-top: 8px; background: #16a34a; color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                            <span class="tv2-check">✓</span> {{ $claimedCount }} {{ __('messages.claimed_count_label') }}
                        </div>
                        @if($isEnabled && $routeExists)
                            <a href="{{ route($meta['route']) }}" style="display: block; margin-top: 6px; color: #16a34a; font-size: 11px; font-weight: 600; text-decoration: none;">
                                + {{ __('messages.' . $meta['btn']) ?? 'Claim More' }} <span class="tv2-btn-arrow">→</span>
                            </a>
                        @endif
                    @elseif($isEnabled && $routeExists)
                        <a href="{{ route($meta['route']) }}" style="display: inline-block; margin-top: 8px; background: #2563eb; color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none;">
                            {{ __('messages.' . $meta['btn']) ?? 'Claim' }} <span class="tv2-btn-arrow">→</span>
                        </a>
                    @else
                        <div style="font-size: 11px; margin-top: 4px; color: #9ca3af;">
                            @if($meta['flag'])
                                {{ __('messages.coming_soon_label') ?? 'Coming Soon' }}
                            @else
                                {{ __('messages.contact_provider_btn') ?? 'Contact Provider' }}
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Exchange Rates anchor --}}
    <div id="exchange-rates" style="scroll-margin-top: 100px;"></div>

        {{-- Passport --}}
    @include('traveler._partials.passport', [
        'passportData' => $passportData ?? null,
        'activeBooking' => $activeBooking ?? null,
        'trekCount' => $trekCount ?? 0,
        'checkinCount' => $checkinCount ?? 0,
    ])
    {{-- Travel Memories + Journey Replay (grid) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="{{ route('traveler.dashboard-v2') }}#memories" style="background: #fff; border-radius: 16px; padding: 20px; text-decoration: none; color: inherit; border: 1px solid #e5e7eb;">
            <div style="font-size: 32px; margin-bottom: 8px;">📸</div>
            <h4 style="font-weight: 700; color: #111827; margin: 0 0 6px;">{{ __('messages.travel_memories') ?? 'Travel Memories' }}</h4>
            <p style="font-size: 13px; color: #6b7280; margin: 0;">{{ __('messages.upload_photos') ?? 'Upload photos from your journey checkpoints.' }}</p>
        </a>
        <a href="{{ route('traveler.journey-replay') }}" style="background: #fff; border-radius: 16px; padding: 20px; text-decoration: none; color: inherit; border: 1px solid #e5e7eb;">
            <div style="font-size: 32px; margin-bottom: 8px;">🎬</div>
            <h4 style="font-weight: 700; color: #111827; margin: 0 0 6px;">{{ __('messages.journey_replay') ?? 'Journey Replay' }}</h4>
            <p style="font-size: 13px; color: #6b7280; margin: 0;">{{ __('messages.relive_journey') ?? 'Relive your Nepal experience.' }}</p>
        </a>
        <a href="{{ route('safety.index') }}" style="background: #fff; border-radius: 16px; padding: 20px; text-decoration: none; color: inherit; border: 1px solid #e5e7eb;">
            <div style="font-size: 32px; margin-bottom: 8px;">🛡️</div>
            <h4 style="font-weight: 700; color: #111827; margin: 0 0 6px;">{{ __('messages.safety_center') ?? 'Safety Center' }}</h4>
            <p style="font-size: 13px; color: #6b7280; margin: 0;">{{ __('messages.safety_tools') ?? 'Safety tools and trek support.' }}</p>
        </a>
    </div>

    {{-- SOS anchor --}}
    <div id="sos" style="scroll-margin-top: 100px;"></div>

    {{-- Emergency SOS --}}
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 18px; padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 18px; font-weight: 700; color: #991b1b; margin: 0 0 8px;">🚨 {{ __('messages.emergency_sos') ?? 'Emergency SOS' }}</h3>
        <p style="color: #b91c1c; font-size: 13px; margin: 0 0 12px;">{{ __('messages.sos_help_text') ?? 'Need help in Nepal? Get immediate assistance.' }}</p>
        <button type="button" onclick="openSosModal()" style="background: #dc2626; color: #fff; border: 0; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;">
            📞 {{ __('messages.call_sos') ?? 'Call SOS' }}
        </button>
    </div>


    {{-- My Bookings (separate card) --}}
    <div id="my-bookings-section" class="tv2-my-bookings-section" style="background: #fff; border-radius: 18px; padding: 20px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #111827; margin: 0;">📅 {{ __('messages.nav_my_bookings') ?? 'My Bookings' }}</h3>
            <a href="{{ route('traveler.my-bookings') }}" style="color: #2563eb; font-size: 12px; font-weight: 600; text-decoration: none;">{{ __('messages.see_all') ?? 'See All' }} <span class="tv2-btn-arrow">→</span></a>
        </div>
        @if($treks && $treks->count() > 0)
            @foreach($treks->take(3) as $trek)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid #f3f4f6; border-radius: 10px; margin-bottom: 8px;">
                    <div>
                        <div style="font-weight: 700; font-size: 13px; color: #111827;">{{ $trek->service->name ?? 'Trek' }}</div>
                        <div style="font-size: 12px; color: #6b7280;">{{ $trek->created_at->translatedFormat('M d, Y') }}</div>
                    </div>
                    <a href="{{ route('traveler.bookings.show', $trek) }}" style="padding: 6px 14px; border: 1px solid #2563eb; color: #2563eb; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none;">{{ __('messages.view') ?? 'View' }}</a>
                </div>
            @endforeach
        @else
            <p style="font-size: 13px; color: #9ca3af; margin: 0;">{{ __('messages.no_trips_yet') ?? 'No bookings yet' }}</p>
        @endif
    </div>

    {{-- Reviews + Tools + Exchange rates grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Ads: Featured for You (ported from legacy) --}}
@include('partials.ad-carousel', ['featuredAds' => $featuredAds ?? collect()])

@include('traveler._partials.reviews', ['reviewsCount' => $reviewsCount ?? 0])
        @include('traveler._partials.exchange-rates', ['exchangeRates' => $exchangeRates ?? collect()])
    </div>

    {{-- Tools & Shortcuts --}}
    <div style="margin-bottom: 24px;">
        @include('traveler._partials.tools')
    </div>


<!-- [AUTO-INJECTED] 6 GAPS SECTIONS (PREMIUM UI) -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 px-4">
    <!-- Status Counters -->
    <div class="bg-gradient-to-br from-blue-50 to-white p-5 rounded-2xl shadow-soft border border-blue-100 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex items-center justify-between">
            <p class="text-xs text-blue-600 font-bold uppercase tracking-wider">Upcoming</p>
            <i class="fa-regular fa-calendar-alt text-blue-400"></i>
        </div>
        <h4 class="text-3xl font-extrabold text-blue-900 mt-2">{{ $statusCounts['upcoming'] ?? 0 }}</h4>
    </div>
    <div class="bg-gradient-to-br from-green-50 to-white p-5 rounded-2xl shadow-soft border border-green-100 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex items-center justify-between">
            <p class="text-xs text-green-600 font-bold uppercase tracking-wider">Active</p>
            <i class="fa-solid fa-plane-departure text-green-400"></i>
        </div>
        <h4 class="text-3xl font-extrabold text-green-900 mt-2">{{ $statusCounts['active'] ?? 0 }}</h4>
    </div>
    <div class="bg-gradient-to-br from-purple-50 to-white p-5 rounded-2xl shadow-soft border border-purple-100 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex items-center justify-between">
            <p class="text-xs text-purple-600 font-bold uppercase tracking-wider">Completed</p>
            <i class="fa-solid fa-check-circle text-purple-400"></i>
        </div>
        <h4 class="text-3xl font-extrabold text-purple-900 mt-2">{{ $statusCounts['completed'] ?? 0 }}</h4>
    </div>
    <div class="bg-gradient-to-br from-yellow-50 to-white p-5 rounded-2xl shadow-soft border border-yellow-100 hover:-translate-y-1 transition-transform duration-300">
        <div class="flex items-center justify-between">
            <p class="text-xs text-yellow-600 font-bold uppercase tracking-wider">Reviews</p>
            <i class="fa-solid fa-star text-yellow-400"></i>
        </div>
        <h4 class="text-3xl font-extrabold text-yellow-900 mt-2">{{ $statusCounts['reviews'] ?? 0 }}</h4>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 px-4">
    <!-- Wholesale -->
    <div class="bg-white rounded-3xl p-6 shadow-float border border-slate-100 flex items-center justify-between group hover:border-purple-200 transition-colors">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Wholesale RFQs</h3>
                <p class="text-sm text-slate-500 font-medium">{{ $wholesale_count ?? 0 }} total</p>
            </div>
        </div>
        <a href="{{ route('wholesale.rfq.index') }}" class="text-white font-bold text-xs bg-gradient-to-r from-slate-800 to-slate-900 px-4 py-2 rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-300">View</a>
    </div>

    <!-- Trek History -->
    <div class="bg-white rounded-3xl p-6 shadow-float border border-slate-100 flex flex-col justify-between">
        <h2 class="text-sm font-bold text-slate-900 mb-3 flex items-center">
            <i class="fa-solid fa-qrcode text-blue-500 mr-2 text-lg"></i>Trek History
        </h2>
        @if(($qr_scans ?? 0) > 0)
            <p class="text-sm text-slate-600 font-medium">You have {{ $qr_scans }} check-ins.</p>
        @else
            <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-4 text-center">
                <p class="text-slate-500 text-xs mb-3 font-medium">No trek history yet.</p>
                <a href="{{ route('traveler.bookings.index') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-xs shadow-md shadow-blue-500/30 hover:bg-blue-700 transition">Scan Checkpoint</a>
            </div>
        @endif
    </div>
<!-- Travel Memories -->
    <div class="bg-white rounded-3xl p-6 shadow-float border border-slate-100" id="memories">
        <h2 class="text-sm font-bold text-slate-900 mb-3 flex items-center">
            <i class="fa-solid fa-images text-purple-500 mr-2 text-lg"></i>Memories
        </h2>
        @if(($user_media_count ?? 0) > 0)
            <p class="text-sm text-slate-600 font-medium">You have {{ $user_media_count }} memories.</p>
        @else
            <form action="{{ route('traveler.checkpoint.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col space-y-3">
                @csrf
                <div class="relative w-full">
                    <input type="file" name="media" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                    <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-3 text-center hover:bg-slate-100 transition">
                        <i class="fa-solid fa-cloud-upload-alt text-slate-400 mb-1"></i>
                        <p class="text-[10px] text-slate-500 font-bold">Choose File to Upload</p>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 py-2 rounded-xl font-bold text-xs shadow-md shadow-purple-500/30 hover:opacity-90 transition">Upload Memory</button>
            </form>
                @if(($userMedia ?? collect())->count() > 0)
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach($userMedia->take(6) as $media)
                            <div class="relative group rounded-lg overflow-hidden border border-slate-200 aspect-square bg-slate-100">
                                @if($media->media_type === 'image')
                                    <img src="{{ Storage::url($media->optimized_path) }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                @endif
                                <form action="{{ route('traveler.memory.delete') }}" method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $media->id }}">
                                    <button type="submit" onclick="return confirm('{{ __('messages.traveler_delete_memory_confirm') ?? 'Delete this memory?' }}')" class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">&times;</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                    @if($userMedia->count() > 6)
                        <p class="text-xs text-slate-400 mt-2">+{{ $userMedia->count() - 6 }} more memories</p>
                    @endif
                @else
                    <p class="text-xs text-slate-400 mt-3">{{ __('messages.traveler_no_memories') ?? 'No memories yet. Upload from your trek checkpoints!' }}</p>
                @endif
        @endif
    </div>
</div>

<!-- Safety Alerts -->
<div class="mt-8 px-4 mb-10">
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-3xl p-6 shadow-soft border border-green-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-green-500 shadow-sm {{ ($unreadAlerts ?? collect())->count() > 0 ? 'animate-pulse' : '' }}">
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                </div>
                <div>
                    <h2 class="text-md font-bold text-green-900 flex items-center gap-2">
                        {{ __('messages.safety_alerts_title') ?? 'Safety Alerts' }}
                        @if(($unreadAlerts ?? collect())->count() > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $unreadAlerts->count() }}</span>
                        @endif
                    </h2>
                    @if(($unreadAlerts ?? collect())->count() === 0)
                        <p class="text-sm text-green-700 font-medium mt-0.5">No active safety alerts in your area. You are safe to travel!</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('safety.index') ?? '#' }}" class="bg-white text-green-700 font-bold text-sm px-6 py-2.5 rounded-xl border border-green-200 shadow-sm hover:shadow-md transition">View Safety Map</a>
        </div>

        @if(($unreadAlerts ?? collect())->count() > 0)
            <div class="space-y-2 max-h-72 overflow-y-auto">
                @foreach($unreadAlerts as $alert)
                    <div class="border-l-4 
                        @if($alert['severity'] === 'critical') border-red-600
                        @elseif($alert['severity'] === 'high') border-orange-500
                        @elseif($alert['severity'] === 'moderate') border-yellow-500
                        @else border-green-500 @endif
                        bg-white p-3 rounded-r-lg shadow-sm">
                        <div class="flex justify-between items-start gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-slate-900">
                                    @if($alert['severity'] === 'critical') 🔴
                                    @elseif($alert['severity'] === 'high') 🟠
                                    @elseif($alert['severity'] === 'moderate') 🟡
                                    @else 🟢 @endif
                                    {{ $alert['incident']['title'] ?? $alert['message'] ?? 'Safety Alert' }}
                                </p>
                                <p class="text-xs text-slate-600 mt-1">{{ $alert['message'] ?? '' }}</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ isset($alert['sent_at']) ? \Carbon\Carbon::parse($alert['sent_at'])->diffForHumans() : 'Just now' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('traveler.alert.read', $alert['id']) }}" class="inline flex-shrink-0">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">{{ __('messages.traveler_mark_read_btn') ?? 'Mark read' }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
<!-- [END AUTO-INJECTED] -->
@endsection