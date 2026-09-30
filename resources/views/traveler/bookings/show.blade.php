@extends('layouts.public')

@section('title', __('messages.traveler_booking_detail_title', ['id' => $booking->id]))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('traveler.dashboard') }}" class="hover:text-blue-600">{{ __('messages.traveler_dashboard') }}</a>
        <span class="mx-2">/</span>
        <span>{{ __('messages.traveler_booking_hash', ['id' => $booking->id]) }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-md border p-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.traveler_booking_hash', ['id' => $booking->id]) }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ $booking->service->name ?? __('messages.na') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($booking->status === 'completed') bg-green-100 text-green-800
                @else bg-red-100 text-red-800 @endif">
                @if($booking->status === 'pending') {{ __('messages.pending') }}
                @elseif($booking->status === 'confirmed') {{ __('messages.confirmed') }}
                @elseif($booking->status === 'completed') {{ __('messages.completed') }}
                @else {{ __('messages.cancelled') }} @endif
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-6 border-t pt-4">
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.traveler_booking_start_date') }}</p>
                <p class="font-medium">{{ $booking->start_date ? $booking->start_date->format('M d, Y') : __('messages.tbd') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">{{ __('messages.traveler_booking_date') }}</p>
                <p class="font-medium">{{ $booking->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-gray-500">{{ __('messages.traveler_booking_provider') }}</p>
                <p class="font-medium">{{ $booking->service->provider->name ?? __('messages.na') }}</p>
            </div>
        </div>

        {{-- PHASE 7E.2 — Payment Methods Display --}}
        @php
            $providerMethods = $booking->service->provider->paymentMethods
                ->where('is_active', true)
                ->sortBy('sort_order');
            $domesticMethods = $providerMethods->whereIn('type', ['bank', 'esewa', 'khalti', 'cash'])->values();
            $internationalMethods = $providerMethods->whereIn('type', ['paypal', 'wise', 'international_bank'])->values();
            $typeIcons = [
                'bank' => ['fas', 'university'], 'esewa' => ['fas', 'mobile-alt'],
                'khalti' => ['fas', 'wallet'], 'cash' => ['fas', 'money-bill-wave'],
                'paypal' => ['fab', 'paypal'], 'wise' => ['fas', 'exchange-alt'],
                'international_bank' => ['fas', 'globe'],
            ];
        @endphp

        <div class="mt-6 border-t pt-6">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-wallet text-blue-600"></i>
                {{ __('messages.traveler_payment_methods_heading') }}
            </h3>

            @if($domesticMethods->isEmpty() && $internationalMethods->isEmpty())
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
                    <p class="text-sm text-blue-800">{{ __('messages.traveler_payment_no_methods') }}</p>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-3 mb-4">
                    <p class="text-xs text-yellow-800 flex items-start gap-2">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <span>{{ __('messages.traveler_payment_disclaimer') }}</span>
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-4 lg:gap-6">
                @foreach([
                    ['methods' => $domesticMethods, 'flag' => '🇳🇵', 'label' => __('messages.traveler_payment_domestic_badge'), 'border' => 'border-l-blue-500'],
                    ['methods' => $internationalMethods, 'flag' => '🌍', 'label' => __('messages.traveler_payment_international_badge'), 'border' => 'border-l-purple-500'],
                ] as $group)
                    @if($group['methods']->isNotEmpty())
                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-2">
                                <span>{{ $group['flag'] }}</span> {{ $group['label'] }}
                            </h4>
                            <div class="grid gap-3">
                                @foreach($group['methods'] as $method)
                                    @php
                                        $icon = $typeIcons[$method->type] ?? ['fas', 'credit-card'];
                                        $typeLabel = __('messages.traveler_payment_' . $method->type);
                                        $copyValues = [];
                                        if ($method->account_number) $copyValues[] = ['label' => __('messages.traveler_payment_account_number'), 'value' => $method->account_number];
                                        if ($method->identifier)     $copyValues[] = ['label' => __('messages.traveler_payment_identifier'),     'value' => $method->identifier];
                                        if ($method->swift_code)     $copyValues[] = ['label' => __('messages.traveler_payment_swift'),           'value' => $method->swift_code];
                                    @endphp
                                    <div class="border border-l-4 {{ $group['border'] }} rounded-lg p-4 bg-white hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                        <div class="flex items-start gap-3 mb-2">
                                            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                                <i class="{{ $icon[0] }} fa-{{ $icon[1] }}"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-gray-800 text-sm">
                                                    {{ $method->label ?: $typeLabel }}
                                                </p>
                                                @if($method->account_name)
                                                    <p class="text-xs text-gray-500 truncate">{{ $method->account_name }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if($method->bank_name)
                                            <div class="text-xs text-gray-600 mb-2">
                                                <span class="text-gray-400">{{ __('messages.traveler_payment_bank_name') }}:</span> {{ $method->bank_name }}
                                            </div>
                                        @endif

                                        @foreach($copyValues as $cv)
                                            <div class="flex items-center justify-between bg-gray-50 rounded px-2 py-1.5 mb-1">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs text-gray-400">{{ $cv['label'] }}</p>
                                                    <p class="text-sm font-mono text-gray-800 truncate">{{ $cv['value'] }}</p>
                                                </div>
                                                <button type="button"
                                                        data-copy-value="{{ $cv['value'] }}"
                                                        data-copy-failed="{{ __('messages.traveler_booking_copy_failed') }}"
                                                        data-copy-not-supported="{{ __('messages.traveler_booking_copy_not_supported') }}"
                                                        onclick="pmCopy(this)"
                                                        title="{{ __('messages.traveler_payment_copy_btn') }}"
                                                        aria-label="{{ __('messages.traveler_payment_copy_btn') }}"
                                                        class="ml-2 p-1.5 text-blue-600 hover:bg-blue-100 rounded text-xs shrink-0 transition-colors duration-200">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        @endforeach

                                        @if($method->qr_image_path)
                                            <div class="mt-2">
                                                <a href="{{ Storage::url($method->qr_image_path) }}" target="_blank">
                                                    <img src="{{ Storage::url($method->qr_image_path) }}"
                                                         alt="QR"
                                                         class="w-24 h-24 border rounded object-contain">
                                                </a>
                                            </div>
                                        @endif

                                        @if($method->instructions)
                                            <div class="mt-2 pt-2 border-t">
                                                <p class="text-xs text-gray-400 mb-0.5">{{ __('messages.traveler_payment_instructions_label') }}</p>
                                                <p class="text-xs text-gray-700">{{ $method->instructions }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
                </div>
                <div class="mt-4 pt-4 border-t">
                    @if($booking->payment_notice_sent_at === null)
                        <form method="POST" action="{{ route('traveler.bookings.notifyPayment', $booking) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full md:w-auto bg-green-600 hover:bg-green-700 hover:brightness-110 text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-check-circle"></i>
                                {{ __('messages.traveler_payment_ive_paid_btn') }}
                            </button>
                        </form>
                    @else
                        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-3">
                            <p class="text-sm text-green-800 flex items-center gap-2 flex-wrap">
                                <i class="fas fa-check-circle"></i>
                                {{ __('messages.traveler_payment_already_notified') }}
                                <span class="text-xs text-green-600">
                                    ({{ $booking->payment_notice_sent_at->diffForHumans() }})
                                </span>
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- 🎯 QR Code + Quick Actions (COMPACT) --}}
        <div class="mt-6 border-t pt-6">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100 p-4 md:p-5 flex flex-col md:flex-row gap-4 md:gap-6 items-center">
                {{-- QR Code --}}
                <img src="{{ route('booking.qr', $booking->id) }}"
                     alt="{{ __('messages.traveler_booking_qr_alt') }}"
                     class="w-28 h-28 border-4 border-white rounded-lg shadow-sm bg-white shrink-0">

                {{-- Info --}}
                <div class="flex-1 min-w-0 text-center md:text-left">
                    <h3 class="font-semibold text-gray-800 flex items-center justify-center md:justify-start gap-2">
                        <i class="fas fa-qrcode text-blue-600"></i>
                        {{ __('messages.traveler_booking_qr_heading') }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ __('messages.traveler_booking_qr_instruction') }}</p>
                </div>

                {{-- Invoice Button --}}
                <a href="{{ route('traveler.bookings.invoice', $booking) }}"
                   target="_blank"
                   class="bg-blue-600 hover:bg-blue-700 hover:brightness-110 text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 shrink-0 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-file-pdf"></i> {{ __('messages.traveler_booking_download_invoice') }}
                </a>
            </div>
        </div>

        {{-- PHASE 4E: Dashboard Rich Data --}}
        @include('traveler.bookings._weather_panel', ['booking' => $booking])
        @include('traveler.bookings._altitude_profile', ['booking' => $booking])

        {{-- Review Section --}}
        @if($booking->review)
            <div class="mt-6 border-t pt-4">
                <h3 class="font-semibold text-gray-700">{{ __('messages.traveler_booking_your_review') }}</h3>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-yellow-500">{{ str_repeat('⭐', $booking->review->rating) }}</span>
                    <span class="text-sm text-gray-500">({{ $booking->review->rating }}/5)</span>
                </div>
                @if($booking->review->comment)
                    <p class="text-gray-600 mt-1">{{ $booking->review->comment }}</p>
                @endif
            </div>
        @elseif($booking->status === 'completed')
            <div class="mt-6 border-t pt-4">
                <a href="{{ route('traveler.reviews.create', $booking) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-star mr-1"></i> {{ __('messages.traveler_booking_write_review') }}
                </a>
            </div>
        @endif

        <div class="mt-6 border-t pt-4">
            <a href="{{ route('traveler.dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                ← {{ __('messages.traveler_booking_back_to_dashboard') }}
            </a>
        </div>
    </div>
</div>

<script>
function pmCopy(btn) {
    var value = btn.getAttribute('data-copy-value') || '';
    var failedMsg = btn.getAttribute('data-copy-failed') || 'Copy failed';
    var unsupportedMsg = btn.getAttribute('data-copy-not-supported') || 'Copy not supported';
    if (!value) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(value).then(function() {
            var icon = btn.querySelector('i');
            if (!icon) return;
            var old = icon.className;
            icon.className = 'fas fa-check text-green-600';
            setTimeout(function() { icon.className = old; }, 1500);
        }).catch(function() { alert(failedMsg + ': ' + value); });
    } else {
        alert(unsupportedMsg + ': ' + value);
    }
}
</script>
@endsection