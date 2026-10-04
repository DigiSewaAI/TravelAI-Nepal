@extends('layouts.provider')

@section('title', __('messages.provider_orders_title'))
@section('header', __('messages.provider_orders_title'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('messages.provider_orders_title') }}</h2>
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2 mb-4 border-b pb-2 overflow-x-auto">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'] as $key => $label)
            <a href="{{ route('provider.orders.index', ['status' => $key]) }}"
               class="px-3 py-1.5 text-sm rounded-lg whitespace-nowrap {{ $status === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $label }} ({{ $counts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Order</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Customer</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Items</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Total</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 text-sm font-medium">{{ $order->order_number }}</td>
                            <td class="py-3 text-sm">{{ $order->user->name ?? $order->contact_name }}</td>
                            <td class="py-3 text-sm">{{ $order->items->count() }}</td>
                            <td class="py-3 text-sm font-medium">
                                {{ $currencyService->format(
                                    $currencyService->convert((float) $order->total, $order->currency, $displayCurrency),
                                    $displayCurrency
                                ) }}
                            </td>
                            <td class="py-3 text-sm">
                                @php $pStatus = $order->getProviderStatusForProvider(auth()->user()->ownProvider()->id); @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($pStatus === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($pStatus === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($pStatus === 'shipped') bg-purple-100 text-purple-800
                                    @elseif($pStatus === 'delivered') bg-green-100 text-green-800
                                    @elseif($pStatus === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($pStatus ?? 'n/a') }}
                                </span>
                            </td>
                            <td class="py-3 text-sm">
                                <a href="{{ route('provider.orders.show', $order) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @else
        <div class="text-center py-12 text-gray-500">
            <p>{{ __('messages.provider_orders_empty') }}</p>
        </div>
    @endif
</div>
@endsection