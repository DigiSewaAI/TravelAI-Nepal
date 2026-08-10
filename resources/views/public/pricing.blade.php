@extends('layouts.public')

@section('title', 'Pricing | TravelAI Nepal')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
            Simple, Transparent Pricing
        </h1>
        <p class="text-xl text-gray-600">
            Choose the perfect plan for your tourism business. Start free, scale as you grow.
        </p>
    </div>

    <!-- Plans Grid -->
    <div class="grid md:grid-cols-4 gap-6 mb-12">
        @foreach($plans as $plan)
        <div class="bg-white rounded-2xl shadow-lg border p-6 hover:shadow-xl transition 
                    {{ $plan->slug === 'free' ? 'border-gray-200' : '' }}
                    {{ $plan->slug === 'professional' ? 'border-blue-500 shadow-blue-100' : '' }}
                    {{ $plan->slug === 'business' ? 'border-purple-500 shadow-purple-100' : '' }}
                    {{ $plan->slug === 'enterprise' ? 'border-amber-500 shadow-amber-100' : '' }}">
            
            @if($plan->slug === 'professional')
                <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</span>
            @endif
            @if($plan->slug === 'enterprise')
                <span class="bg-amber-600 text-white text-xs font-bold px-3 py-1 rounded-full">Best Value</span>
            @endif

            <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $plan->name }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $plan->description }}</p>

            <div class="mt-4">
                @if($plan->price_monthly === null)
                    <span class="text-3xl font-bold text-gray-900">Custom</span>
                @elseif($plan->price_monthly == 0)
                    <span class="text-3xl font-bold text-gray-900">Free</span>
                @else
                    <span class="text-3xl font-bold text-gray-900">${{ number_format($plan->price_monthly, 2) }}</span>
                    <span class="text-gray-500 text-sm">/ month</span>
                    <div class="text-sm text-gray-400 mt-1">
                        or <span class="font-semibold">${{ number_format($plan->price_yearly, 2) }}</span> / year
                    </div>
                @endif
            </div>

            <!-- Features -->
            <ul class="mt-6 space-y-2 text-sm">
                @php
                    $features = $plan->features ?? [];
                @endphp
                @foreach($features as $feature)
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>

            <!-- Limits -->
            @if($plan->limits)
                <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                    @php $limits = $plan->limits; @endphp
                    @if(isset($limits['max_listings']))
                        <div>{{ $limits['max_listings'] == -1 ? '∞' : $limits['max_listings'] }} Services</div>
                    @endif
                    @if(isset($limits['max_staff']))
                        <div>{{ $limits['max_staff'] == -1 ? '∞' : $limits['max_staff'] }} Staff Users</div>
                    @endif
                    @if(isset($limits['max_ai_requests']))
                        <div>{{ $limits['max_ai_requests'] == -1 ? '∞' : $limits['max_ai_requests'] }} AI Requests/mo</div>
                    @endif
                </div>
            @endif

            <!-- CTA Button -->
            <div class="mt-6">
                @if($plan->slug === 'free')
                    <a href="{{ route('register') }}" 
                       class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-xl transition">
                        Get Started Free
                    </a>
                @else
                    <a href="{{ route('register', ['plan' => $plan->slug]) }}" 
                       class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md">
                        Choose {{ $plan->name }}
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- FAQ Section -->
    <div class="max-w-3xl mx-auto mt-12">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-8">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-800">Can I switch plans later?</h3>
                <p class="text-gray-600 mt-1">Yes, you can upgrade, downgrade, or cancel your plan at any time from your dashboard.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-800">What payment methods do you accept?</h3>
                <p class="text-gray-600 mt-1">We accept credit/debit cards, eSewa, Khalti, and bank transfers.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-800">Is there a free trial?</h3>
                <p class="text-gray-600 mt-1">Yes, the Free plan is always available with basic features. Upgrade anytime.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-800">What happens if I exceed my plan limits?</h3>
                <p class="text-gray-600 mt-1">You'll be notified and can upgrade to a higher plan to continue using the service.</p>
            </div>
        </div>
    </div>
</div>
@endsection