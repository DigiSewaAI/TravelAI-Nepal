@extends('layouts.public')

@section('title', 'Pricing | TravelAI Nepal')
@section('content')

{{-- ========== HERO SECTION ========== --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold">Simple, Transparent Pricing</h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        Choose the perfect plan for your tourism business. Start free, scale as you grow.
    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    {{-- ========== PLANS GRID ========== --}}
    <div class="grid md:grid-cols-4 gap-6">
        @foreach($plans as $plan)
        <div class="bg-white rounded-2xl shadow-lg border p-6 hover:shadow-xl transition group
                    {{ $plan->slug === 'free' ? 'border-gray-200' : '' }}
                    {{ $plan->slug === 'professional' ? 'border-blue-500 shadow-blue-100 relative' : '' }}
                    {{ $plan->slug === 'business' ? 'border-purple-500 shadow-purple-100' : '' }}
                    {{ $plan->slug === 'enterprise' ? 'border-amber-500 shadow-amber-100' : '' }}">
            
            @if($plan->slug === 'professional')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full">Most Popular</span>
            @endif
            @if($plan->slug === 'enterprise')
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-600 text-white text-xs font-bold px-4 py-1 rounded-full">Best Value</span>
            @endif

            <div class="mt-2">
                <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-gray-500 text-sm mt-1">{{ $plan->description }}</p>
            </div>

            <div class="mt-4">
                @if($plan->price_monthly === null)
                    <span class="text-3xl font-bold text-gray-900">Custom</span>
                @elseif($plan->price_monthly == 0)
                    <span class="text-3xl font-bold text-green-600">Free</span>
                @else
                    <span class="text-3xl font-bold text-gray-900">${{ number_format($plan->price_monthly, 2) }}</span>
                    <span class="text-gray-500 text-sm">/ month</span>
                    <div class="text-sm text-gray-400 mt-1">
                        or <span class="font-semibold">${{ number_format($plan->price_yearly, 2) }}</span> / year
                    </div>
                @endif
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
                        <div>📦 {{ $limits['max_listings'] == -1 ? '∞' : $limits['max_listings'] }} Services</div>
                    @endif
                    @if(isset($limits['max_staff']))
                        <div>👥 {{ $limits['max_staff'] == -1 ? '∞' : $limits['max_staff'] }} Staff</div>
                    @endif
                    @if(isset($limits['max_ai_requests']))
                        <div>🤖 {{ $limits['max_ai_requests'] == -1 ? '∞' : $limits['max_ai_requests'] }} AI Requests/mo</div>
                    @endif
                </div>
            @endif

            {{-- CTA Button --}}
            <div class="mt-6">
                @if($plan->slug === 'free')
                    <a href="{{ route('register') }}" 
                       class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-xl transition">
                        Get Started Free
                    </a>
                @elseif($plan->slug === 'enterprise')
                    <a href="mailto:sales@travelai.com?subject=Enterprise%20Plan%20Inquiry" 
                       class="w-full block text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 rounded-xl transition shadow-md">
                        Contact for Pricing
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

    {{-- ========== PRICING COMPARISON TABLE ========== --}}
    <div class="mt-16 bg-white rounded-2xl shadow-lg border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-800">📊 Plan Comparison</h3>
        </div>
        <div class="overflow-x-auto p-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 font-semibold text-gray-700">Feature</th>
                        @foreach($plans as $plan)
                            <th class="text-center py-3 font-semibold text-gray-700">{{ $plan->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">Price</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3 font-medium">
                                @if($plan->price_monthly === null) Custom
                                @elseif($plan->price_monthly == 0) Free
                                @else ${{ number_format($plan->price_monthly, 0) }}/mo
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">Services</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">{{ $plan->limits['max_listings'] ?? '∞' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">Staff Users</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">{{ $plan->limits['max_staff'] ?? '∞' }}</td>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600">AI Requests</td>
                        @foreach($plans as $plan)
                            <td class="text-center py-3">{{ $plan->limits['max_ai_requests'] ?? '∞' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="py-3 text-gray-600">Custom Logo</td>
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
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-8">Frequently Asked Questions</h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">Can I switch plans later?</h3>
                <p class="text-gray-600 mt-1">Yes, you can upgrade, downgrade, or cancel your plan at any time from your dashboard.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">What payment methods do you accept?</h3>
                <p class="text-gray-600 mt-1">We accept credit/debit cards, eSewa, Khalti, and bank transfers.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">Is there a free trial?</h3>
                <p class="text-gray-600 mt-1">Yes, the Free plan is always available with basic features. Upgrade anytime.</p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800">What happens if I exceed my plan limits?</h3>
                <p class="text-gray-600 mt-1">You'll be notified and can upgrade to a higher plan to continue using the service.</p>
            </div>
        </div>
    </div>
</div>
@endsection