@php
    $user = auth()->user();
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? __('messages.greeting_morning') : ($hour < 18 ? __('messages.greeting_afternoon') : __('messages.greeting_evening'));
    if (!isset($counts) || !is_array($counts)) $counts = [];
@endphp

<div class="tv2-hero" style="background: linear-gradient(to right, rgba(15,23,42,0.75) 0%, rgba(15,23,42,0.35) 50%, rgba(15,23,42,0.10) 100%), url('{{ asset('images/hero-bg.jpg') }}') center/cover; border-radius: 18px; padding: 52px 44px; min-height: 420px; color: #fff; margin-bottom: 24px; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -40px; right: -40px; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <h1 style="font-size: 32px; font-weight: 800; margin: 0 0 8px;"><span class="tv2-greeting-line">{{ $greeting ?? 'Hello' }},</span> <span class="tv2-name-line">{{ $user->name ?? 'Traveler' }} 👋</span></h1>
        <p style="font-size: 15px; opacity: 0.9; margin: 0 0 24px;">{{ __('messages.traveler_ready_for_adventure') ?? 'Ready for your next Nepal adventure?' }}</p>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="background: rgba(255,255,255,0.18); padding: 10px 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🏔️</span>
                <span style="font-size: 14px; font-weight: 600;">{{ $counts['trekking'] ?? 0 }} {{ ($counts['trekking'] ?? 0) == 1 ? __('messages.trek_singular') : __('messages.trek_plural') }}</span>
            </div>
            <div style="background: rgba(255,255,255,0.18); padding: 10px 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">📅</span>
                <span style="font-size: 14px; font-weight: 600;">{{ $counts['trekking'] ?? 0 }} {{ ($counts['trekking'] ?? 0) == 1 ? __('messages.booking_singular') : __('messages.booking_plural') }}</span>
            </div>
            <div style="background: rgba(255,255,255,0.18); padding: 10px 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">⭐</span>
                <span style="font-size: 14px; font-weight: 600;">{{ $counts['reviews'] ?? 0 }} {{ ($counts['reviews'] ?? 0) == 1 ? __('messages.review_singular') : __('messages.review_plural') }}</span>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;">
            <a href="{{ url('/') }}#ai-planner" style="background: #fff; color: #1e40af; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                🤖 {{ __('messages.plan_with_ai') ?? 'Plan with AI' }}
            </a>
            <a href="{{ route('public.services.index') }}" style="background: rgba(255,255,255,0.18); color: #fff; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.3);">
                🧭 {{ __('messages.explore_nepal') ?? 'Explore Nepal' }}
            </a>
        </div>
    </div>
    <div style="position: absolute; top: 30px; right: 30px; z-index: 2;">
        <div class="tv2-weather-inline">
            @include('traveler._partials.weather', ['weatherData' => $weatherData ?? null])
        </div>
    </div>
</div>