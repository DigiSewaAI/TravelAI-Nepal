@extends('layouts.provider')

@section('title', __('messages.products_title'))
@section('header', __('messages.products_title'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getDisplayCurrency();
@endphp

<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">{{ __('messages.products_title') }}</h2>
            <p class="text-xs text-gray-500 mt-1">
                @if($maxProducts === -1)
                    {{ __('messages.products_limit_unlimited') }}
                @else
                    {{ __('messages.products_limit_label', ['used' => $counts['all'], 'max' => $maxProducts]) }}
                @endif
            </p>
        </div>
        @if($canCreateMore)
            <a href="{{ route('provider.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-plus mr-1"></i> {{ __('messages.products_create_btn') }}
            </a>
        @endif
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2 mb-4 border-b pb-2 overflow-x-auto">
        <a href="{{ route('provider.products.index') }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ !$type ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ __('messages.products_filter_all') }} ({{ $counts['all'] }})
        </a>
        <a href="{{ route('provider.products.index', ['type' => 'shop']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $type === 'shop' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ __('messages.products_filter_shop') }} ({{ $counts['shop'] }})
        </a>
        <a href="{{ route('provider.products.index', ['type' => 'rental']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $type === 'rental' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ __('messages.products_filter_rental') }} ({{ $counts['rental'] }})
        </a>
        <a href="{{ route('provider.products.index', ['type' => 'wholesale']) }}"
           class="px-3 py-1.5 text-sm rounded-lg {{ $type === 'wholesale' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            {{ __('messages.products_filter_wholesale') }} ({{ $counts['wholesale'] }})
        </a>
    </div>

    @if($products->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.product_name') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.product_type') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.product_price') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.product_status') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.actions') ?? 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium">{{ $product->name }}</td>
                        <td class="py-3 text-sm">
                            @php
                                $badgeMap = [
                                    'shop'      => 'bg-purple-100 text-purple-800',
                                    'rental'    => 'bg-indigo-100 text-indigo-800',
                                    'wholesale' => 'bg-amber-100 text-amber-800',
                                ];
                                $badge = $badgeMap[$product->product_type] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                {{ __('messages.product_type_' . $product->product_type) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            @php
                                $baseCurrency = $product->currency ?? 'USD';
                                $displayPrice = $currencyService->convert((float) $product->price, $baseCurrency, $displayCurrency);
                            @endphp
                            {{ $currencyService->format($displayPrice, $displayCurrency) }}
                        </td>
                        <td class="py-3 text-sm">
                            @if($product->status === 'active')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">{{ __('messages.product_status_active') }}</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">{{ __('messages.product_status_inactive') }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('provider.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2">
                                {{ __('messages.products_edit_btn') }}
                            </a>
                            <form method="POST" action="{{ route('provider.products.destroy', $product) }}" class="inline"
                                  onsubmit="return confirm('{{ __('messages.products_delete_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">
                                    {{ __('messages.products_delete_btn') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            <p>{{ __('messages.products_no_items') }}</p>
        </div>
    @endif
</div>
@endsection