@extends('layouts.public')

@section('title', $order->order_number . ' | ' . __('messages.app_name'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<section class="max-w-4xl mx-auto px-6 md:px-10 py-8">

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 border border-green-300 rounded-lg">{{ session('success') }}</div>
    @endif

    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('orders.index') }}" class="hover:text-blue-600">{{ __('messages.orders_title') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $order->order_number }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-sm border p-6">

        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <span class="inline-block px-3 py-1.5 rounded-full text-sm font-semibold
                @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                @elseif($order->status === 'delivered') bg-green-100 text-green-800
                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        {{-- Items --}}
        <div class="border-t pt-4">
            <h2 class="font-semibold text-gray-900 mb-3">Items</h2>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between items-center bg-gray-50 rounded-lg p-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst($item->product_type) }} · Qty: {{ $item->quantity }}
                                @if($item->rental_days) · {{ $item->rental_days }} days @endif
                            </p>
                            @if($item->provider)
                                <p class="text-xs text-gray-500">by {{ $item->provider->name }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">
                                Status: <span class="font-semibold">{{ ucfirst($item->provider_status ?? 'pending') }}</span>
                            </p>

                            {{-- PATH-3C C2: Rental return status + button --}}
                            @if($item->product_type === 'rental')
                                @if($item->isReturnConfirmed())
                                    <div class="mt-2 p-2 bg-green-50 border-l-2 border-green-500 rounded text-xs">
                                        <p class="text-green-800 font-medium">
                                            {{ __('messages.return_confirmed') }} — {{ ucfirst($item->return_condition) }}
                                        </p>
                                        @if($item->deposit_refund_amount !== null)
                                            <p class="text-green-700 mt-0.5">
                                                {{ __('messages.return_refund_amount') }}: NPR {{ number_format($item->deposit_refund_amount, 2) }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- PATH-3C C4: refund breakdown --}}
                                    <div class="mt-2 p-3 bg-gray-50 rounded text-xs space-y-1">
                                        <div class="font-semibold text-gray-700 mb-1">
                                            {{ __('messages.return_refund_breakdown') }}
                                        </div>
                                        <div class="flex justify-between">
                                            <span>{{ __('messages.return_deposit_paid') }}</span>
                                            <span>{{ $currencyService->format($currencyService->convert($item->rental_deposit * $item->quantity, 'NPR', $displayCurrency), $displayCurrency) }}</span>
                                        </div>
                                        @if($item->return_condition === 'damaged')
                                            <div class="flex justify-between text-orange-600">
                                                <span>{{ __('messages.return_damage_deduction') }}
                                                    ({{ $item->product->rentalDetail->damage_deposit_pct ?? 100 }}%)
                                                </span>
                                                <span>- {{ $currencyService->format($currencyService->convert($item->rental_deposit * $item->quantity * (($item->product->rentalDetail->damage_deposit_pct ?? 100) / 100), 'NPR', $displayCurrency), $displayCurrency) }}</span>
                                            </div>
                                        @elseif($item->return_condition === 'lost')
                                            <div class="flex justify-between text-red-600">
                                                <span>{{ __('messages.return_lost_deduction') }}</span>
                                                <span>- {{ $currencyService->format($currencyService->convert($item->rental_deposit * $item->quantity, 'NPR', $displayCurrency), $displayCurrency) }}</span>
                                            </div>
                                        @endif
                                        @if($item->calculateLateFee() > 0)
                                            <div class="flex justify-between text-orange-600">
                                                <span>{{ __('messages.return_late_fee') }}</span>
                                                <span>- {{ $currencyService->format($currencyService->convert($item->calculateLateFee(), 'NPR', $displayCurrency), $displayCurrency) }}</span>
                                            </div>
                                        @endif
                                        <div class="flex justify-between font-bold text-green-700 pt-1 border-t">
                                            <span>{{ __('messages.return_net_refund') }}</span>
                                            <span>{{ $currencyService->format($currencyService->convert($item->deposit_refund_amount ?? 0, 'NPR', $displayCurrency), $displayCurrency) }}</span>
                                        </div>
                                    </div>
                                @elseif($item->hasReturnRequest())
                                    <div class="mt-2 p-2 bg-yellow-50 border-l-2 border-yellow-500 rounded text-xs">
                                        <p class="text-yellow-800 font-medium">{{ __('messages.return_requested') }}</p>
                                        <p class="text-yellow-700">{{ __('messages.return_pending_provider') }}</p>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('orders.items.return', [$order, $item]) }}" class="mt-2">
                                        @csrf
                                        <button type="submit" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded">
                                            <i class="fas fa-undo mr-1"></i> {{ __('messages.return_request_btn') }}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                        <span class="font-bold text-blue-600">
                            {{ $currencyService->format(
                                $currencyService->convert((float) $item->line_total, $order->currency, $displayCurrency),
                                $displayCurrency
                            ) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Contact + Shipping --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 border-t pt-4">
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Contact</h3>
                <p class="text-sm text-gray-700">{{ $order->contact_name }}</p>
                <p class="text-sm text-gray-500">{{ $order->contact_email }}</p>
                @if($order->contact_phone)
                    <p class="text-sm text-gray-500">{{ $order->contact_phone }}</p>
                @endif
            </div>
            @if($order->shipping_address || $order->shipping_city)
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Shipping</h3>
                    @if($order->shipping_address)
                        <p class="text-sm text-gray-700">{{ $order->shipping_address }}</p>
                    @endif
                    <p class="text-sm text-gray-500">
                        {{ $order->shipping_city }}{{ $order->shipping_city && $order->shipping_country ? ', ' : '' }}{{ $order->shipping_country }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Totals --}}
        <div class="border-t mt-6 pt-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">{{ __('messages.cart_subtotal') }}</span>
                <span>{{ $currencyService->format($currencyService->convert((float)$order->subtotal, $order->currency, $displayCurrency), $displayCurrency) }}</span>
            </div>
            @if($order->shipping_fee > 0)
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-gray-600">Shipping</span>
                    <span>{{ $currencyService->format($currencyService->convert((float)$order->shipping_fee, $order->currency, $displayCurrency), $displayCurrency) }}</span>
                </div>
            @endif
            @if($order->deposit_total > 0)
                <div class="flex justify-between text-sm mt-1 text-blue-700">
                    <span>{{ __('messages.order_deposit_total') }}</span>
                    <span>{{ $currencyService->format($currencyService->convert((float)$order->deposit_total, $order->currency, $displayCurrency), $displayCurrency) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-lg font-bold mt-3 pt-3 border-t">
                <span>Total</span>
                <span class="text-blue-600">
                    {{ $currencyService->format($currencyService->convert((float)$order->total, $order->currency, $displayCurrency), $displayCurrency) }}
                </span>
            </div>
        </div>

        @if($order->notes)
            <div class="border-t mt-6 pt-4">
                <h3 class="font-semibold text-gray-900 mb-2">Notes</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $order->notes }}</p>
            </div>
        @endif

        {{-- PATH-3C C1: Deposit tracking --}}
        @if($order->deposit_total > 0)
            <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                <h3 class="font-semibold text-blue-900 mb-2">{{ __('messages.order_deposit_held') }}</h3>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">{{ __('messages.order_deposit_total') }}</span>
                    <span class="font-mono font-medium">
                        {{ $currencyService->format($currencyService->convert((float)$order->deposit_total, $order->currency, $displayCurrency), $displayCurrency) }}
                    </span>
                </div>
                @if($order->deposit_refunded_amount > 0)
                    <div class="flex justify-between text-sm mt-1 text-green-700">
                        <span>{{ __('messages.order_deposit_refunded') }}</span>
                        <span class="font-mono font-medium">
                            {{ $currencyService->format($currencyService->convert((float)$order->deposit_refunded_amount, $order->currency, $displayCurrency), $displayCurrency) }}
                        </span>
                    </div>
                @endif
                <div class="flex justify-between text-sm mt-2 pt-2 border-t border-blue-200 font-semibold">
                    <span>{{ __('messages.order_deposit_held') }}</span>
                    <span class="font-mono text-blue-700">
                        {{ $currencyService->format($currencyService->convert($order->deposit_held, $order->currency, $displayCurrency), $displayCurrency) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ __('messages.order_deposit_hint') }}</p>
            </div>
        @endif

        {{-- PATH-3B B7: Timeline --}}
        <div class="mt-6 border-t pt-6">
            @include('partials.order-timeline', ['order' => $order])
        </div>

        {{-- PATH-3B B5: Payment Section --}}
        <div class="mt-6 border-t pt-6">
            <h3 class="text-lg font-semibold mb-3">
                {{ __('messages.order_payment_section') }}
            </h3>

            @if(session('info'))
                <div class="mb-3 p-3 bg-blue-100 text-blue-800 rounded-lg text-sm">{{ session('info') }}</div>
            @endif
            @if(session('success'))
                <div class="mb-3 p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($order->isPaymentVerified())
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <p class="text-green-800">
                        <i class="fas fa-check-circle"></i>
                        {{ __('messages.order_payment_verified') }}
                        ({{ $order->payment_verified_at->diffForHumans() }})
                    </p>
                </div>
            @elseif($order->isPaymentNotified())
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <p class="text-yellow-800">
                        <i class="fas fa-clock"></i>
                        {{ __('messages.order_payment_pending_verification') }}
                        ({{ $order->payment_notice_sent_at->diffForHumans() }})
                    </p>
                </div>
            @else
                @if($order->payment_methods_snapshot)
                    <div class="space-y-4 mb-4">
                        @foreach($order->payment_methods_snapshot as $pid => $data)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <h4 class="font-semibold text-sm mb-2">
                                    {{ __('messages.order_payment_for_provider') }}: {{ $data['provider_name'] }}
                                </h4>
                                @foreach($data['methods'] as $method)
                                    <div class="text-sm mb-2 p-2 bg-white rounded">
                                        <div class="font-medium">{{ $method['label'] ?? ucfirst($method['type']) }}</div>
                                        @if(!empty($method['account_name']))
                                            <div class="text-gray-600">{{ $method['account_name'] }}</div>
                                        @endif
                                        @if(!empty($method['account_number']))
                                            <div class="text-gray-600 font-mono">{{ $method['account_number'] }}</div>
                                        @endif
                                        @if(!empty($method['identifier']))
                                            <div class="text-gray-600">{{ $method['identifier'] }}</div>
                                        @endif
                                        @if(!empty($method['instructions']))
                                            <div class="text-xs text-gray-500 mt-1">{{ $method['instructions'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-4">{{ __('messages.order_payment_no_methods') }}</p>
                @endif

                <form method="POST" action="{{ route('orders.notifyPayment', $order) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            {{ __('messages.order_payment_reference') }}
                        </label>
                        <input type="text" name="payment_reference" maxlength="100" required
                               value="{{ old('payment_reference') }}"
                               class="w-full border rounded-lg px-3 py-2">
                        @error('payment_reference') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            {{ __('messages.order_payment_note') }}
                        </label>
                        <textarea name="payment_note" maxlength="500" rows="2"
                                  class="w-full border rounded-lg px-3 py-2">{{ old('payment_note') }}</textarea>
                    </div>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-medium">
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ __('messages.order_payment_ive_paid') }}
                    </button>
                </form>
            @endif
        </div>

    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-blue-600">← {{ __('messages.orders_title') }}</a>
    </div>

</section>
@endsection