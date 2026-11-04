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
    ])

    {{-- Coming Soon Categories --}}
    <div style="background: #fff; border-radius: 18px; padding: 24px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
        <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 16px;">
            🔗 {{ __('messages.coming_soon_personal_access') ?? 'Personal booking access coming soon' }}
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px;">
            @foreach(['Hotel', 'Restaurant', 'Transport', 'ArtCraft', 'MoneyChanger'] as $cat)
                <div style="border: 1px dashed #e5e7eb; border-radius: 10px; padding: 16px; text-align: center; color: #9ca3af;">
                    <div style="font-size: 28px; margin-bottom: 6px;">📦</div>
                    <div style="font-weight: 600; font-size: 13px;">{{ $cat }}</div>
                    <div style="font-size: 11px; margin-top: 4px;">{{ __('messages.not_linked_yet') ?? 'Not linked to your account yet' }}</div>
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

    {{-- Travel Memories anchor --}}
    <div id="memories" style="scroll-margin-top: 100px;"></div>

    {{-- Travel Memories + Journey Replay (grid) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <a href="{{ route('traveler.dashboard') }}#memories" style="background: #fff; border-radius: 16px; padding: 20px; text-decoration: none; color: inherit; border: 1px solid #e5e7eb;">
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
        <button type="button" onclick="alert('SOS workflow — will be wired in a future commit')" style="background: #dc2626; color: #fff; border: 0; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;">
            📞 {{ __('messages.call_sos') ?? 'Call SOS' }}
        </button>
    </div>


    {{-- My Bookings (separate card) --}}
    <div id="my-bookings-section" class="tv2-my-bookings-section" style="background: #fff; border-radius: 18px; padding: 20px; margin-bottom: 24px; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="font-size: 15px; font-weight: 800; color: #111827; margin: 0;">📅 {{ __('messages.nav_my_bookings') ?? 'My Bookings' }}</h3>
            <a href="{{ route('traveler.my-bookings') }}" style="color: #2563eb; font-size: 12px; font-weight: 600; text-decoration: none;">{{ __('messages.see_all') ?? 'See All' }} →</a>
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
        @include('traveler._partials.reviews', ['reviewsCount' => $reviewsCount ?? 0])
        @include('traveler._partials.exchange-rates', ['exchangeRates' => $exchangeRates ?? collect()])
    </div>

    {{-- Tools & Shortcuts --}}
    <div style="margin-bottom: 24px;">
        @include('traveler._partials.tools')
    </div>
@endsection