@extends('layouts.public')

@section('title', __('messages.pricing_page_title'))
@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">{{ __('messages.pricing_hero_title') }}</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        {{ __('messages.pricing_hero_subtitle') }}
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== GLOBAL BILLING TOGGLE ========== --}}
    <div class="text-center mb-12">
        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 shadow-sm" role="group" aria-label="{{ __('messages.billing_interval') }}">
            <button type="button" class="billing-toggle px-6 py-2 rounded-full text-sm font-semibold transition bg-blue-600 text-white" data-interval="monthly" aria-pressed="true">{{ __('messages.monthly') }}</button>
            <button type="button" class="billing-toggle px-6 py-2 rounded-full text-sm font-semibold transition text-gray-600 hover:bg-gray-200" data-interval="yearly" aria-pressed="false">{{ __('messages.yearly') }}</button>
        </div>
        <div id="billing-badge" class="mt-2 text-green-600 text-sm font-medium hidden">🎁 {{ __('messages.two_months_free') }}</div>
    </div>

    {{-- ========== PLANS GRID ========== --}}
    <div class="grid md:grid-cols-4 gap-6">
        @foreach($plans as $plan)
        <div class="bg-white rounded-2xl shadow-lg border p-6 hover:shadow-xl transition group
                    {{ $plan->slug === 'free' ? 'border-gray-200' : '' }}
                    {{ $plan->slug === 'professional' ? 'border-blue-500 shadow-blue-100 relative' : '' }}
                    {{ $plan->slug === 'business' ? 'border-purple-500 shadow-purple-100' : '' }}
                    {{ $plan->slug === 'enterprise' ? 'border-amber-500 shadow-amber-100' : '' }}">
            
            @if($plan->slug === 'professional')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full">{{ __('messages.most_popular') }}</span>
            @endif
            @if($plan->slug === 'enterprise')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-600 text-white text-xs font-bold px-4 py-1 rounded-full">{{ __('messages.best_value') }}</span>
            @endif

            <div class="mt-2">
                <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-gray-500 text-sm mt-1">{{ $plan->description }}</p>
            </div>

            {{-- Price display with toggle support --}}
<div class="mt-4">
    {{-- Monthly price --}}
    <div class="price-amount" data-interval="monthly">
        @if($plan->price_monthly === null)
            <span class="text-3xl font-bold text-gray-900">{{ __('messages.custom') }}</span>
        @elseif($plan->price_monthly == 0)
            <span class="text-3xl font-bold text-green-600">{{ __('messages.free') }}</span>
        @else
            <span class="text-3xl font-bold text-gray-900">Rs. {{ number_format($plan->price_monthly, 0) }}</span>
            <span class="text-gray-500 text-sm">/ {{ __('messages.month') }}</span>
        @endif
    </div>

    {{-- Yearly price --}}
    <div class="price-amount hidden" data-interval="yearly">
        @if($plan->price_yearly === null)
            <span class="text-3xl font-bold text-gray-900">{{ __('messages.custom') }}</span>
        @elseif($plan->price_yearly == 0)
            <span class="text-3xl font-bold text-green-600">{{ __('messages.free') }}</span>
        @else
            <span class="text-3xl font-bold text-gray-900">Rs. {{ number_format($plan->price_yearly, 0) }}</span>
            <span class="text-gray-500 text-sm">/ {{ __('messages.year') }}</span>
            @if($plan->price_monthly !== null && $plan->price_monthly > 0)
                <div class="text-sm text-gray-400 mt-1">≈ Rs. {{ number_format($plan->price_yearly / 12, 0) }}/{{ __('messages.month') }}</div>
                <div class="text-xs text-green-600 font-medium">🟢 {{ __('messages.save_two_months') }}</div>
            @endif
        @endif
    </div>
</div>

            {{-- Features --}}
            <ul class="mt-6 space-y-2 text-sm">
                @php $features = $plan->features ?? []; @endphp
                @foreach($features as $feature)
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>

            {{-- Limits --}}
            @if($plan->limits)
                <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                    @php $limits = $plan->limits; @endphp
                    @if(isset($limits['max_listings']))
                        <div>📦 {{ $limits['max_listings'] == -1 ? '∞' : $limits['max_listings'] }} {{ __('messages.services') }}</div>
                    @endif
                    @if(isset($limits['max_staff']))
                        <div>👥 {{ $limits['max_staff'] == -1 ? '∞' : $limits['max_staff'] }} {{ __('messages.staff') }}</div>
                    @endif
                    @if(isset($limits['max_ai_requests']))
                        <div>🤖 {{ $limits['max_ai_requests'] == -1 ? '∞' : $limits['max_ai_requests'] }} {{ __('messages.ai_requests_mo') }}</div>
                    @endif
                </div>
            @endif

            {{-- CTA Button with interval --}}
            <div class="mt-6">
                @if($plan->slug === 'free')
                    <a href="{{ route('register') }}" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-xl transition">{{ __('messages.get_started_free') }}</a>
                @elseif($plan->slug === 'enterprise')
                    <a href="mailto:sales@travelai.com?subject=Enterprise%20Plan%20Inquiry" class="w-full block text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 rounded-xl transition shadow-md">{{ __('messages.contact_for_pricing') }}</a>
                @else
                    <a href="{{ route('register', ['plan' => $plan->slug, 'billing_interval' => 'monthly']) }}" class="cta-button w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md" data-interval="monthly">{{ __('messages.choose_plan_btn', ['name' => $plan->name]) }}</a>
                    <a href="{{ route('register', ['plan' => $plan->slug, 'billing_interval' => 'yearly']) }}" class="cta-button hidden w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md" data-interval="yearly">{{ __('messages.choose_plan_btn', ['name' => $plan->name]) }}</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- ========== PLAN COMPARISON TABLE ========== --}}
    <div class="mt-16 bg-white rounded-2xl shadow-lg border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-800">📊 {{ __('messages.plan_comparison') }}</h3>
        </div>
        <div class="overflow-x-auto p-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 font-semibold text-gray-700">{{ __('messages.feature') }}</th>
                        @foreach($plans as $plan)
                            <th class="text-center py-3 font-semibold text-gray-700">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
    <td class="py-3 text-gray-600">{{ __('messages.price') }}</td>
    @foreach($plans as $plan)
        <td class="text-center py-3 font-medium">
            <span class="price-amount" data-interval="monthly">
                @if($plan->price_monthly === null) {{ __('messages.custom') }}
                @elseif($plan->price_monthly == 0) {{ __('messages.free') }}
                @else Rs. {{ number_format($plan->price_monthly, 0) }}/mo
                @endif
            </span>
            <span class="price-amount hidden" data-interval="yearly">
                @if($plan->price_yearly === null) {{ __('messages.custom') }}
                @elseif($plan->price_yearly == 0) {{ __('messages.free') }}
                @else Rs. {{ number_format($plan->price_yearly, 0) }}/yr
                @endif
            </span>
        </td>
    @endforeach
</tr>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">{{ __('messages.services') }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">{{ $plan->limits['max_listings'] ?? '∞' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">{{ __('messages.staff_users') }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">{{ $plan->limits['max_staff'] ?? '∞' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">{{ __('messages.ai_requests') }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">{{ $plan->limits['max_ai_requests'] ?? '∞' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="py-3 text-gray-600">{{ __('messages.custom_logo') }}</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">
                                @if(in_array('Custom Logo', $plan->features ?? []))
                                    <i class="fas fa-check-circle text-green-500"></i>
                                @else
                                    <i class="fas fa-times-circle text-gray-300"></i>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== FAQ ========== --}}
    <div class="max-w-3xl mx-auto mt-16">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-8">{{ __('messages.faq_title') }}</h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">{{ __('messages.faq_q1') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.faq_a1') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">{{ __('messages.faq_q2') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.faq_a2') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">{{ __('messages.faq_q3') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.faq_a3') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">{{ __('messages.faq_q4') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.faq_a4') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtns = document.querySelectorAll('.billing-toggle');
        const badge = document.getElementById('billing-badge');

        function setInterval(interval) {
            toggleBtns.forEach(btn => {
                const isActive = btn.dataset.interval === interval;
                btn.classList.toggle('bg-blue-600', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('text-gray-600', !isActive);
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            document.querySelectorAll('.price-amount').forEach(el => {
                el.classList.toggle('hidden', el.dataset.interval !== interval);
            });
            document.querySelectorAll('.cta-button').forEach(el => {
                el.classList.toggle('hidden', el.dataset.interval !== interval);
            });

            badge.classList.toggle('hidden', interval !== 'yearly');
        }

        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                setInterval(this.dataset.interval);
            });
        });

        setInterval('monthly');
    });
</script>
@endsection