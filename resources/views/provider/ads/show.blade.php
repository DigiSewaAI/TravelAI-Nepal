@extends('layouts.provider')

@section('title', $ad->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    <a href="{{ route('provider.ads.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-amber-600 mb-4">
        <i class="fas fa-arrow-left"></i> {{ __('messages.ads_back') }}
    </a>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Ad card --}}
    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden mb-5">
        <img src="{{ Storage::url($ad->image_path) }}" class="w-full h-48 object-cover">

        <div class="p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $ad->title }}</h1>
                    @if($ad->description)
                        <p class="text-sm text-gray-600 mt-1">{{ $ad->description }}</p>
                    @endif
                </div>

                @php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'pending_review' => 'bg-amber-100 text-amber-700',
                        'payment_pending' => 'bg-blue-100 text-blue-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'expired' => 'bg-gray-100 text-gray-600',
                    ];
                    $color = $statusColors[$ad->status] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $color }}">
                    {{ ucfirst(str_replace('_', ' ', $ad->status)) }}
                </span>
            </div>

            {{-- Rejection reason --}}
            @if($ad->status === 'rejected' && $ad->rejection_reason)
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-800">
                    <strong>{{ __('messages.ads_rejection_reason') }}:</strong>
                    {{ $ad->rejection_reason }}
                </div>
            @endif

            {{-- Details --}}
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_duration_label') }}</div>
                    <div class="font-bold">{{ $ad->duration_days }} {{ __('messages.ads_days') }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_price_label') }}</div>
                    <div class="font-bold">Rs. {{ number_format($ad->price_paid) }}</div>
                </div>
                @if($ad->start_date)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_started_at') }}</div>
                        <div class="font-medium">{{ $ad->start_date->format('M d, Y') }}</div>
                    </div>
                @endif
                @if($ad->end_date)
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_expires_at') }}</div>
                        <div class="font-medium">{{ $ad->end_date->format('M d, Y') }}</div>
                    </div>
                @endif
            </div>

            {{-- Stats (if active or expired) --}}
            @if(in_array($ad->status, ['active', 'expired']))
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($ad->impressions) }}</div>
                        <div class="text-xs text-blue-700 uppercase">{{ __('messages.ads_views') }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-green-600">{{ number_format($ad->clicks) }}</div>
                        <div class="text-xs text-green-700 uppercase">{{ __('messages.ads_clicks') }}</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $ad->ctr }}%</div>
                        <div class="text-xs text-purple-700 uppercase">{{ __('messages.ads_ctr') }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Payment info --}}
    @if($ad->payments->count() > 0)
        @php $payment = $ad->payments->first(); @endphp
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fas fa-receipt text-amber-500"></i> {{ __('messages.ads_payment_section') }}
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_payment_method_label') }}</div>
                    <div class="font-medium">{{ $payment->payment_method }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_status') }}</div>
                    <div class="font-medium">{{ ucfirst($payment->status) }}</div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection