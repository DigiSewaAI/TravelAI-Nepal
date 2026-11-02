@extends('layouts.public')

@section('title', __('messages.pricing_meta_title'))
@section('meta_description', __('messages.pricing_meta_description'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== HERO ========== --}}
    <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ __('messages.pricing_hero_title') }}</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">{{ __('messages.pricing_8_verticals_subtitle') }}</p>
    </div>

    {{-- ========== 8 VERTICALS OVERVIEW ========== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 mb-12">
        @foreach(['trekking' => '🏔️', 'hotel' => '🏨', 'restaurant' => '🍜', 'transport' => '🚐', 'individual' => '🧑‍✈️', 'money_changer' => '💱', 'art_craft' => '🎨', 'inventory' => '📦'] as $slug => $icon)
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center hover:border-blue-400 hover:shadow-md transition">
                <div class="text-3xl mb-1">{{ $icon }}</div>
                <div class="text-xs font-bold text-gray-700">{{ __('messages.verticals.' . $slug) }}</div>
            </div>
        @endforeach
    </div>

    {{-- ========== BILLING TOGGLE ========== --}}
    <div class="text-center mb-12">
        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 shadow-sm">
            <button type="button" class="billing-toggle px-6 py-2 rounded-full text-sm font-semibold transition bg-blue-600 text-white" data-interval="monthly">{{ __('messages.monthly') }}</button>
            <button type="button" class="billing-toggle px-6 py-2 rounded-full text-sm font-semibold transition text-gray-600 hover:bg-gray-200" data-interval="yearly">{{ __('messages.yearly') }}</button>
        </div>
        <div id="billing-badge" class="mt-2 text-green-600 text-sm font-medium hidden">{{ __('messages.two_months_free') }}</div>
    </div>

    {{-- ========== PLAN CARDS ========== --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        @foreach($plans as $plan)
        <div class="bg-white rounded-2xl shadow-lg border p-6 hover:shadow-xl transition
                    {{ $plan->slug === 'free' ? 'border-gray-200' : '' }}
                    {{ $plan->slug === 'professional' ? 'border-blue-500 shadow-blue-100 relative' : '' }}
                    {{ $plan->slug === 'business' ? 'border-purple-500 shadow-purple-100' : '' }}
                    {{ $plan->slug === 'enterprise' ? 'border-amber-500 shadow-amber-100' : '' }}">

            @if($plan->slug === 'professional')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full whitespace-nowrap">{{ __('messages.most_popular') }}</span>
            @endif
            @if($plan->slug === 'enterprise')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-600 text-white text-xs font-bold px-4 py-1 rounded-full whitespace-nowrap">{{ __('messages.best_value') }}</span>
            @endif

            <div class="mt-2">
                <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-gray-500 text-sm mt-1">{{ __('messages.plans.' . $plan->slug . '.description') }}</p>
            </div>

            {{-- Price --}}
            <div class="mt-4">
                <div class="price-amount" data-interval="monthly">
                    @if($plan->price_monthly === null)
                        <span class="text-3xl font-bold text-gray-900">{{ __('messages.custom') }}</span>
                    @elseif($plan->price_monthly == 0)
                        <span class="text-3xl font-bold text-green-600">{{ __('messages.free') }}</span>
                        <span class="text-gray-500 text-sm block">{{ __('messages.forever') }}</span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">Rs. {{ number_format($plan->price_monthly, 0) }}</span>
                        <div class="text-xs text-gray-500 mt-1">≈ ${{ number_format($plan->price_monthly / 152.5, 2) }} USD</div>
                        <span class="text-gray-500 text-sm">/ {{ __('messages.month') }}</span>
                    @endif
                </div>
                <div class="price-amount hidden" data-interval="yearly">
                    @if($plan->price_yearly === null)
                        <span class="text-3xl font-bold text-gray-900">{{ __('messages.custom') }}</span>
                    @elseif($plan->price_yearly == 0)
                        <span class="text-3xl font-bold text-green-600">{{ __('messages.free') }}</span>
                        <span class="text-gray-500 text-sm block">{{ __('messages.forever') }}</span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">Rs. {{ number_format($plan->price_yearly, 0) }}</span>
                        <div class="text-xs text-gray-500 mt-1">≈ ${{ number_format($plan->price_yearly / 152.5, 2) }} USD</div>
                        <span class="text-gray-500 text-sm">/ {{ __('messages.year') }}</span>
                        @if($plan->price_monthly !== null && $plan->price_monthly > 0)
                            <div class="text-sm text-gray-400 mt-1">≈ Rs. {{ number_format($plan->price_yearly / 12, 0) }}/{{ __('messages.month') }}</div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- PER-VERTICAL LIMITS (grouped) --}}
            @php $limits = $plan->limits ?? []; @endphp
            <div class="mt-5 pt-4 border-t text-xs">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('messages.per_vertical_limits') }}</div>

                @php
                    $verticals = [
                        'trekking' => [
                            'icon' => '🏔️',
                            'limits' => [
                                ['max_treks', 'treks'],
                                ['max_itinerary_days', 'itinerary_days'],
                            ],
                        ],
                        'hotel' => [
                            'icon' => '🏨',
                            'limits' => [
                                ['max_hotels', 'hotels'],
                                ['max_rooms', 'rooms'],
                            ],
                        ],
                        'restaurant' => [
                            'icon' => '🍜',
                            'limits' => [
                                ['max_menu_items', 'menu_items'],
                                ['max_tables', 'tables'],
                            ],
                        ],
                        'transport' => [
                            'icon' => '🚐',
                            'limits' => [
                                ['max_vehicles', 'vehicles'],
                                ['max_routes', 'routes'],
                            ],
                        ],
                        'individual' => [
                            'icon' => '🧑‍✈️',
                            'limits' => [
                                ['max_guides', 'guides'],
                            ],
                        ],
                        'money_changer' => [
                            'icon' => '💱',
                            'limits' => [
                                ['max_currencies', 'currencies'],
                            ],
                        ],
                        'art_craft' => [
                            'icon' => '🎨',
                            'limits' => [
                                ['max_artworks', 'artworks'],
                            ],
                        ],
                        'inventory' => [
                            'icon' => '📦',
                            'limits' => [
                                ['max_products', 'products'],
                            ],
                        ],
                    ];
                @endphp

                @foreach($verticals as $slug => $v)
                    @php
                        $hasLimit = collect($v['limits'])->contains(fn($l) => isset($limits[$l[0]]));
                    @endphp
                    @if($hasLimit)
                        <div class="mb-2">
                            <div class="font-semibold text-gray-700 flex items-center gap-1">
                                <span>{{ $v['icon'] }}</span>
                                <span class="text-[11px]">{{ __('messages.verticals.' . $slug) }}</span>
                            </div>
                            @foreach($v['limits'] as [$key, $labelKey])
                                @if(isset($limits[$key]))
                                    <div class="flex justify-between pl-4 py-0.5 text-gray-600">
                                        <span>{{ __('messages.limit_labels.' . $labelKey) }}</span>
                                        <span class="font-semibold">{{ $limits[$key] == -1 ? __('messages.unlimited') : $limits[$key] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @endforeach

                {{-- Shared limits --}}
                <div class="mt-3 pt-2 border-t">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">{{ __('messages.shared_limits') }}</div>
                    @if(isset($limits['max_staff']))
                        <div class="flex justify-between py-0.5 text-gray-600"><span>👥 {{ __('messages.staff') }}</span><span class="font-semibold">{{ $limits['max_staff'] == -1 ? __('messages.unlimited') : $limits['max_staff'] }}</span></div>
                    @endif
                    @if(isset($limits['max_bookings']))
                        <div class="flex justify-between py-0.5 text-gray-600"><span>📅 {{ __('messages.trekking_bookings_mo') }}</span><span class="font-semibold">{{ $limits['max_bookings'] == -1 ? __('messages.unlimited') : $limits['max_bookings'] }}</span></div>
                    @endif
                    @if(isset($limits['max_ai_requests']))
                        <div class="flex justify-between py-0.5 text-gray-600"><span>🤖 {{ __('messages.ai_requests') }}</span><span class="font-semibold">{{ $limits['max_ai_requests'] == -1 ? __('messages.unlimited') : $limits['max_ai_requests'] }}</span></div>
                    @endif
                </div>
            </div>

            {{-- CTA --}}
            <div class="mt-6">
                @if($plan->slug === 'free')
                    <a href="{{ route('register', ['account_type' => 'provider', 'plan' => $plan->slug, 'billing_interval' => 'monthly']) }}" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-xl transition">{{ __('messages.get_started_free') }}</a>
                @elseif($plan->slug === 'enterprise')
                    <a href="{{ route('public.contact-sales') }}" class="w-full block text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 rounded-xl transition shadow-md">{{ __('messages.contact_for_pricing') }}</a>
                @else
                    <a href="{{ route('register', ['account_type' => 'provider', 'plan' => $plan->slug, 'billing_interval' => 'monthly']) }}" class="cta-button w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md" data-interval="monthly">{{ __('messages.choose_plan_btn', ['name' => $plan->name]) }}</a>
                    <a href="{{ route('register', ['account_type' => 'provider', 'plan' => $plan->slug, 'billing_interval' => 'yearly']) }}" class="cta-button hidden w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md" data-interval="yearly">{{ __('messages.choose_plan_btn', ['name' => $plan->name]) }}</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- ========== COMPARISON TABLE (Per-Vertical) ========== --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 mb-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ __('messages.plan_comparison') }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b-2 border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-3 font-bold">{{ __('messages.plan_features') }}</th>
                        @foreach($plans as $plan)
                            <th class="text-center py-3 px-2 font-bold {{ $plan->slug === 'professional' ? 'text-blue-600' : 'text-gray-700' }}">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Price row --}}
                    <tr class="border-b border-gray-100">
                        <td class="py-2 px-3 font-semibold">{{ __('messages.plan_price') }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-2 px-2">
                                @if($plan->price_monthly === null) {{ __('messages.custom') }}
                                @elseif($plan->price_monthly == 0) {{ __('messages.free') }}
                                @else Rs. {{ number_format($plan->price_monthly, 0) }}/mo
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    @php
                        $sections = [
                            'trekking' => [
                                'icon' => '🏔️',
                                'rows' => [
                                    ['max_treks', 'treks'],
                                    ['max_itinerary_days', 'itinerary_days'],
                                ],
                            ],
                            'hotel' => [
                                'icon' => '🏨',
                                'rows' => [
                                    ['max_hotels', 'hotels'],
                                    ['max_rooms', 'rooms'],
                                ],
                            ],
                            'restaurant' => [
                                'icon' => '🍜',
                                'rows' => [
                                    ['max_menu_items', 'menu_items'],
                                    ['max_tables', 'tables'],
                                ],
                            ],
                            'transport' => [
                                'icon' => '🚐',
                                'rows' => [
                                    ['max_vehicles', 'vehicles'],
                                    ['max_routes', 'routes'],
                                ],
                            ],
                            'individual' => [
                                'icon' => '🧑‍✈️',
                                'rows' => [
                                    ['max_guides', 'guides'],
                                ],
                            ],
                            'money_changer' => [
                                'icon' => '💱',
                                'rows' => [
                                    ['max_currencies', 'currencies'],
                                ],
                            ],
                            'art_craft' => [
                                'icon' => '🎨',
                                'rows' => [
                                    ['max_artworks', 'artworks'],
                                ],
                            ],
                            'inventory' => [
                                'icon' => '📦',
                                'rows' => [
                                    ['max_products', 'products'],
                                ],
                            ],
                        ];
                    @endphp

                    @foreach($sections as $slug => $section)
                        <tr class="bg-gray-100">
                            <td colspan="{{ count($plans) + 1 }}" class="py-2 px-3 font-bold text-gray-800 text-sm">
                                {{ $section['icon'] }} {{ __('messages.verticals.' . $slug) }}
                            </td>
                        </tr>
                        @foreach($section['rows'] as [$key, $labelKey])
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-2 px-3 pl-6">{{ __('messages.limit_labels.' . $labelKey) }}</td>
                                @foreach($plans as $plan)
                                    @php $val = ($plan->limits ?? [])[$key] ?? null; @endphp
                                    <td class="text-center py-2 px-2 font-semibold">
                                        @if($val === -1) {{ __('messages.unlimited') }}
                                        @elseif($val === null) —
                                        @else {{ $val }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach

                    {{-- Shared --}}
                    <tr class="bg-gray-100">
                        <td colspan="{{ count($plans) + 1 }}" class="py-2 px-3 font-bold text-gray-800 text-sm">🔧 {{ __('messages.shared_limits') }}</td>
                    </tr>
                    @foreach([['max_staff', 'Staff Users'], ['max_bookings', __('messages.trekking_bookings_mo')], ['max_ai_requests', 'AI Requests/mo']] as [$key, $label])
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 px-3 pl-6">{{ $label }}</td>
                            @foreach($plans as $plan)
                                @php $val = ($plan->limits ?? [])[$key] ?? null; @endphp
                                <td class="text-center py-2 px-2 font-semibold">
                                    @if($val === -1) {{ __('messages.unlimited') }}
                                    @elseif($val === null) —
                                    @else {{ $val }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    {{-- Features row --}}
                    <tr class="border-b border-gray-100">
                        <td class="py-2 px-3">{{ __('messages.custom_logo_feature') }}</td>
                        @foreach($plans as $plan)
                            @php
                                $hasLogo = collect($plan->features ?? [])->contains(fn($f) => stripos($f, 'logo') !== false);
                            @endphp
                            <td class="text-center py-2 px-2">{{ $hasLogo ? '✅' : '—' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        {{-- AI Features Note --}}
    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:16px 20px;margin-top:16px;font-size:13px;color:#075985">
        <strong>ℹ️ AI Features:</strong> {{ __('messages.ai_features_note') }}
    </div>
    </div>

    {{-- ========== FAQ ========== --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <details class="border-b pb-3">
                <summary class="font-semibold cursor-pointer">Can I use multiple verticals in one plan?</summary>
                <p class="mt-2 text-sm text-gray-600">Yes! Each plan covers all 8 verticals — Trekking, Hotel, Restaurant, Transport, Individual, Money Changer, Art Craft, and Inventory. Your limits are per-vertical (e.g., Free gets 3 treks + 1 hotel + 5 products simultaneously).</p>
            </details>
            <details class="border-b pb-3">
                <summary class="font-semibold cursor-pointer">Can I switch plans later?</summary>
                <p class="mt-2 text-sm text-gray-600">Yes, you can upgrade, downgrade, or cancel at any time from your dashboard.</p>
            </details>
            <details class="border-b pb-3">
                <summary class="font-semibold cursor-pointer">What payment methods do you accept?</summary>
                <p class="mt-2 text-sm text-gray-600">We accept bank transfer, eSewa, and Khalti. Payments are manually verified before plan activation.</p>
            </details>
            <details class="border-b pb-3">
                <summary class="font-semibold cursor-pointer">What happens if I exceed my plan limits?</summary>
                <p class="mt-2 text-sm text-gray-600">You'll be notified and can upgrade to a higher plan to continue using the service.</p>
            </details>
            <details>
                <summary class="font-semibold cursor-pointer">What about AI requests across verticals?</summary>
                <p class="mt-2 text-sm text-gray-600">All AI features (trek itinerary, quotation, future vertical-specific AI) share the same monthly quota.</p>
            </details>
        </div>
    </div>
</div>

<script>
    // Billing toggle
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.billing-toggle');
        const priceAmounts = document.querySelectorAll('.price-amount');
        const ctaButtons = document.querySelectorAll('.cta-button');
        const badge = document.getElementById('billing-badge');

        function setInterval_(interval) {
            toggles.forEach(t => {
                t.classList.toggle('bg-blue-600', t.dataset.interval === interval);
                t.classList.toggle('text-white', t.dataset.interval === interval);
                t.classList.toggle('text-gray-600', t.dataset.interval !== interval);
            });
            priceAmounts.forEach(p => {
                p.classList.toggle('hidden', p.dataset.interval !== interval);
            });
            ctaButtons.forEach(c => {
                c.classList.toggle('hidden', c.dataset.interval !== interval);
            });
            if (badge) badge.classList.toggle('hidden', interval !== 'yearly');
        }

        toggles.forEach(t => t.addEventListener('click', () => setInterval_(t.dataset.interval)));
        setInterval_('monthly');
    });
</script>
@endsection