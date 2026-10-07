@extends('layouts.provider')

@section('title', __('messages.ads_menu'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-bullhorn text-amber-500"></i>
                {{ __('messages.ads_menu') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.ads_subtitle') }}</p>
        </div>
        <a href="{{ route('provider.ads.create') }}"
           class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition shadow-md">
            <i class="fas fa-plus"></i> {{ __('messages.ads_buy_new') }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_status_active') }}</div>
            <div class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] }}</div>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_status_pending') }}</div>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_status_rejected') }}</div>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</div>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <div class="text-xs text-gray-500 uppercase">{{ __('messages.ads_status_expired') }}</div>
            <div class="text-2xl font-bold text-gray-500 mt-1">{{ $stats['expired'] }}</div>
        </div>
    </div>

    {{-- Ads list --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @if($ads->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($ads as $ad)
                    <a href="{{ route('provider.ads.show', $ad) }}"
                       class="block hover:bg-gray-50 transition">
                        <div class="p-4 flex flex-wrap items-center gap-4">
                            <img src="{{ Storage::url($ad->image_path) }}"
                                 class="w-24 h-16 object-cover rounded-lg flex-shrink-0">

                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 truncate">{{ $ad->title }}</h3>
                                <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-gray-500">
                                    <span>{{ $ad->duration_days }} {{ __('messages.ads_days') }}</span>
                                    <span>Rs. {{ number_format($ad->price_paid) }}</span>
                                    <span>{{ $ad->impressions }} views</span>
                                    <span>{{ $ad->clicks }} clicks</span>
                                </div>
                            </div>

                            {{-- Status badge --}}
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-700',
                                    'pending_review' => 'bg-amber-100 text-amber-700',
                                    'payment_pending' => 'bg-blue-100 text-blue-700',
                                    'approved' => 'bg-blue-100 text-blue-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'expired' => 'bg-gray-100 text-gray-600',
                                ];
                                $color = $statusColors[$ad->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $ad->status)) }}
                            </span>

                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $ads->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <div class="w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bullhorn text-amber-500 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-800">{{ __('messages.ads_empty') }}</h3>
                <p class="text-sm text-gray-500 mt-1 mb-4">{{ __('messages.ads_empty_sub') }}</p>
                <a href="{{ route('provider.ads.create') }}"
                   class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg font-semibold">
                    <i class="fas fa-plus"></i> {{ __('messages.ads_buy_new') }}
                </a>
            </div>
        @endif
    </div>

</div>
@endsection