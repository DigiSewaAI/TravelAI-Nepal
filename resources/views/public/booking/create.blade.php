@extends('layouts.public')

@section('title', __('messages.booking_create_page_title', ['name' => $service->name]))

@push('head')
<meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:text-blue-600">{{ __('messages.home') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.index') }}" class="hover:text-blue-600">{{ __('messages.explore') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('public.services.show', $service->slug) }}" class="hover:text-blue-600">{{ $service->name }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('messages.book') }}</span>
    </nav>

    <div class="grid md:grid-cols-3 gap-8">
        <!-- Booking Form -->
        <div class="md:col-span-2 bg-white rounded-2xl shadow-md border p-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.booking_form_heading', ['name' => $service->name]) }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ __('messages.booking_form_subtitle') }}</p>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mt-4 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                        <form method="POST" action="{{ route('public.services.book', $service->slug) }}" class="mt-6 space-y-4">
                @csrf

                {{-- PROVIDER-ITINERARY-09B-04: Departure selection (only when eligible departures exist) --}}
                @if(isset($eligibleDepartures) && $eligibleDepartures->count() > 0)
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_departure_label') }} *</label>
                        <select name="departure_id" id="departure_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('departure_id') border-red-500 @enderror">
                            <option value="">{{ __('messages.booking_form_departure_placeholder') }}</option>
                            @foreach($eligibleDepartures as $dep)
                                @php
                                    $reserved  = (int) ($dep->reserved_seats ?? 0);
                                    $remaining = max(0, $dep->capacity - $reserved);
                                @endphp
                                <option value="{{ $dep->id }}"
                                        data-remaining="{{ $remaining }}"
                                        {{ old('departure_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->start_date->format('M d, Y') }} → {{ $dep->end_date->format('M d, Y') }}
                                    ({{ $remaining }} {{ __('messages.seats') }} {{ __('messages.departure_available') }})
                                </option>
                            @endforeach
                        </select>
                        @error('departure_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_guest_count_label') }} *</label>
                        <input type="number" name="guest_count" id="guest_count"
                               value="{{ old('guest_count', 1) }}"
                               required min="1"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('guest_count') border-red-500 @enderror">
                        @error('guest_count')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1" id="guest_count_hint"></p>
                    </div>
                @endif

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_full_name') }} *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_email') }} *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_phone') }} *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_start_date') }} *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">{{ __('messages.booking_form_special_requests') }}</label>
                    <textarea name="message" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-md hover:shadow-lg">
                    <i class="fas fa-check-circle mr-2"></i> {{ __('messages.booking_form_confirm_btn') }}
                </button>
            </form>
        </div>

        <!-- Service Summary -->
        <div class="bg-gray-50 rounded-2xl border p-6 h-fit">
            <h3 class="font-bold text-gray-800 text-lg">{{ __('messages.booking_summary_heading') }}</h3>
            <div class="mt-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('messages.service') }}</span>
                    <span class="font-medium">{{ $service->name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('messages.provider') }}</span>
                    <span class="font-medium">{{ $service->provider->name ?? __('messages.na') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('messages.category') }}</span>
                    <span class="font-medium">{{ $service->category->name ?? __('messages.na') }}</span>
                </div>
                @if($service->trekDetail)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('messages.booking_duration') }}</span>
                        <span class="font-medium">{{ $service->trekDetail->duration_days }} {{ __('messages.days') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('messages.booking_difficulty') }}</span>
                        <span class="font-medium">{{ ucfirst($service->trekDetail->difficulty) }}</span>
                    </div>
                @endif
                <hr>
                <div class="flex justify-between text-lg font-bold">
                    <span>{{ __('messages.total') }}</span>
                    @php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
    $baseCurrency = $service->currency ?? 'USD';
    $displayPrice = $currencyService->convert($service->price, $baseCurrency, $displayCurrency);
    $formattedPrice = $currencyService->format($displayPrice, $displayCurrency);
@endphp
<span class="text-blue-600">{{ $formattedPrice }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-4"><i class="fas fa-lock mr-1"></i> {{ __('messages.booking_secure_notice') }}</p>
        </div>
        </div>
</div>

{{-- PROVIDER-ITINERARY-09B-04: guest_count UI hint --}}
@if(isset($eligibleDepartures) && $eligibleDepartures->count() > 0)
<script>
    (function () {
        var select = document.getElementById('departure_id');
        var input  = document.getElementById('guest_count');
        var hint   = document.getElementById('guest_count_hint');

        if (!select || !input || !hint) return;

        function updateLimit() {
            var opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) {
                input.removeAttribute('max');
                hint.textContent = '';
                return;
            }
            var remaining = parseInt(opt.dataset.remaining || '0', 10);
            input.max = remaining;
            hint.textContent = 'Max ' + remaining + ' for this departure';
            if (parseInt(input.value || '1', 10) > remaining) {
                input.value = remaining;
            }
        }

        select.addEventListener('change', updateLimit);
        updateLimit();
    })();
</script>
@endif
@endsection