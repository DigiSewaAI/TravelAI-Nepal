@php
    $pd = $passportData ?? null;
    $isPublic = false;
    if (is_array($pd) && isset($pd['is_public'])) $isPublic = (bool) $pd['is_public'];
    elseif (is_object($pd) && isset($pd->is_public)) $isPublic = (bool) $pd->is_public;

    $publicUrl = null;
    if (is_array($pd) && !empty($pd['public_url'])) $publicUrl = $pd['public_url'];
    elseif (is_object($pd) && !empty($pd->public_url)) $publicUrl = $pd->public_url;

    $ab = $activeBooking ?? null;
@endphp

<style>
    .tv2-passport-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 24px;
        border: 1px solid rgba(255,255,255,0.15);
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 60%, #2563eb 100%);
        background-attachment: fixed;
        box-shadow: 0 10px 30px rgba(30,58,138,0.25);
    }
    .tv2-passport-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: url('{{ asset('images/passport-bg.jpg') }}');
        background-size: cover;
        background-position: center;
        opacity: 0.90;
        z-index: 0;
    }
    .tv2-passport-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(30,58,138,0.20) 0%, rgba(37,99,235,0.10) 100%);
        z-index: 1;
    }
    .tv2-passport-inner {
        position: relative;
        z-index: 2;
        padding: 20px 24px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 32px;
        align-items: center;
        color: #fff;
    }

    /* Realistic passport book */
    .tv2-passport-book {
        width: 130px;
        height: 175px;
        position: relative;
        transform: rotate(-8deg);
        transform-style: preserve-3d;
        perspective: 800px;
    }
    .tv2-passport-cover {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 40%, #7c2d12 100%);
        border-radius: 6px 10px 10px 6px;
        box-shadow:
            0 15px 35px rgba(0,0,0,0.45),
            0 5px 15px rgba(0,0,0,0.3),
            inset 0 0 25px rgba(0,0,0,0.3),
            inset 2px 0 3px rgba(255,255,255,0.1);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 18px 12px;
        color: #fbbf24;
        overflow: hidden;
    }
    /* Spine */
    .tv2-passport-cover::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 8px;
        background: linear-gradient(90deg, #450a0a 0%, #6b1515 50%, #450a0a 100%);
        border-radius: 6px 0 0 6px;
        box-shadow: inset -1px 0 2px rgba(0,0,0,0.5);
    }
    /* Gold border frame */
    .tv2-passport-cover::after {
        content: '';
        position: absolute;
        inset: 10px 10px 10px 16px;
        border: 1.5px solid rgba(251,191,36,0.4);
        border-radius: 3px;
        pointer-events: none;
    }
    .tv2-passport-label {
        font-size: 8px;
        font-weight: 800;
        letter-spacing: 2.5px;
        margin-bottom: 10px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }
    .tv2-passport-emblem {
        font-size: 38px;
        margin: 8px 0;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
    }
    .tv2-passport-country {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.5px;
        line-height: 1.2;
        text-align: center;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }
    .tv2-passport-subtitle {
        position: absolute;
        bottom: 8px;
        font-size: 7px;
        letter-spacing: 1.5px;
        opacity: 0.75;
    }
    /* Passport reflection */
    .tv2-passport-book::after {
        content: '';
        position: absolute;
        top: -10px;
        right: -10px;
        width: 60px;
        height: 60px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .tv2-passport-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .tv2-passport-title h3 {
        font-size: 18px;
        font-weight: 800;
        color: #fff;
        margin: 0;
        text-shadow: 0 2px 8px rgba(0,0,0,0.6);
    }
    .tv2-passport-status {
        display: flex;
        gap: 14px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        align-items: center;
        color: #fff;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-toggle-btn {
        background: none;
        border: 0;
        color: #fbbf24;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
    }
    .tv2-passport-subtitle-text {
        font-size: 13px;
        color: #f1f5f9;
        margin: 0 0 14px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 16px;
        font-size: 12px;
        color: #fff;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-stats .val {
        font-weight: 800;
        font-size: 18px;
        color: #fbbf24;
    }
    .tv2-passport-active {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 10px;
        width: fit-content;
        max-width: 320px;
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .tv2-passport-active-label {
        font-size: 10px;
        font-weight: 700;
        color: #fbbf24;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-active-name {
        font-weight: 700;
        font-size: 13px;
        color: #fff;
        margin-bottom: 2px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-active-meta {
        font-size: 11px;
        color: #f1f5f9;
        margin-bottom: 6px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-badge {
        background: #16a34a;
        color: #fff;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        margin-left: 6px;
    }
    .tv2-passport-active-link {
        color: #fbbf24;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .tv2-passport-cta {
        display: inline-block;
        background: #fbbf24;
        color: #78350f;
        padding: 11px 22px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 800;
        font-size: 13px;
        box-shadow: 0 4px 12px rgba(251,191,36,0.35);
        transition: all 0.2s;
    }
    .tv2-passport-cta:hover {
        background: #f59e0b;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(251,191,36,0.45);
    }
</style>

<div id="passport-section" class="tv2-passport-card">
    <div class="tv2-passport-inner">

        <div>
            <div class="tv2-passport-title">
                <span style="font-size: 24px;">🎒</span>
                <h3>{{ __('messages.my_digital_trek_passport') ?? 'My Digital Trek Passport' }}</h3>
            </div>

            <div class="tv2-passport-status">
                @if($isPublic)
                    <span style="color: #4ade80; display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-globe"></i> {{ __('messages.public') ?? 'Public' }}
                    </span>
                @else
                    <span style="display: flex; align-items: center; gap: 4px;">
                        <i class="fas fa-lock"></i> {{ __('messages.private') ?? 'Private' }}
                    </span>
                @endif
                <form method="POST" action="{{ route('traveler.passport.toggle') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="tv2-passport-toggle-btn">
                        {{ $isPublic ? (__('messages.make_private') ?? 'Make Private') : (__('messages.share_publicly') ?? 'Share Publicly') }}
                    </button>
                </form>
            </div>

            <p class="tv2-passport-subtitle-text">{{ __('messages.your_trekking_achievements') ?? 'Your trekking achievements in one place' }}</p>

            <div class="tv2-passport-stats">
                <div><span class="val">{{ $trekCount ?? 0 }}</span> {{ __('messages.traveler_quick_treks') ?? 'Treks' }}</div>
                <div><span class="val">{{ $checkinCount ?? 0 }}</span> {{ __('messages.check_ins') ?? 'Check-ins' }}</div>
            </div>

            @if($ab)
            <div class="tv2-passport-active">
                <div class="tv2-passport-active-label">{{ __('messages.my_active_trip') ?? 'My Active Trip' }}</div>
                <div class="tv2-passport-active-name">{{ $ab->service->name ?? 'Trek' }}</div>
                <div class="tv2-passport-active-meta">
                    {{ $ab->created_at->translatedFormat('M d, Y') }}
                    <span class="tv2-passport-badge">{{ ucfirst($ab->status) }}</span>
                </div>
                <a href="{{ route('traveler.bookings.show', $ab) }}" class="tv2-passport-active-link">{{ __('messages.view_trek_passport') ?? 'View Trek Passport' }} →</a>
            </div>
            @endif

            <a href="{{ route('traveler.passport') }}" class="tv2-passport-cta">
                {{ __('messages.view_full_passport') ?? 'View Full Passport' }} →
            </a>
        </div>

        {{-- Realistic tilted passport book --}}
        <div class="tv2-passport-book">
            <div class="tv2-passport-cover">
                <div class="tv2-passport-label">PASSPORT</div>
                <div class="tv2-passport-emblem">🏔️</div>
                <div class="tv2-passport-country">TRAVELAI<br>NEPAL</div>
                <div class="tv2-passport-subtitle">DIGITAL EDITION</div>
            </div>
        </div>

    </div>
</div>