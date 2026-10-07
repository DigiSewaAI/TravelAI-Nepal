@extends('layouts.admin')

@section('title', 'Ad Analytics')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    <a href="{{ route('admin.ads.show', $ad) }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-amber-600 mb-4">
        <i class="fas fa-arrow-left"></i> Back to Ad
    </a>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $ad->title }}</h1>
    <p class="text-sm text-gray-500 mb-6">Analytics (last 30 days)</p>

    {{-- Main stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-5">
            <div class="text-xs text-gray-500 uppercase">Total Views</div>
            <div class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($ad->impressions) }}</div>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <div class="text-xs text-gray-500 uppercase">Total Clicks</div>
            <div class="text-3xl font-bold text-green-600 mt-1">{{ number_format($ad->clicks) }}</div>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <div class="text-xs text-gray-500 uppercase">Overall CTR</div>
            <div class="text-3xl font-bold text-purple-600 mt-1">{{ $ad->ctr }}%</div>
        </div>
        <div class="bg-white rounded-xl border p-5">
            <div class="text-xs text-gray-500 uppercase">Last 30d CTR</div>
            @php
                $ctr30 = $last30['impressions'] > 0
                    ? round(($last30['clicks'] / $last30['impressions']) * 100, 2)
                    : 0;
            @endphp
            <div class="text-3xl font-bold text-amber-600 mt-1">{{ $ctr30 }}%</div>
        </div>
    </div>

    {{-- Last 30 days --}}
    <div class="bg-white rounded-xl border p-5 mb-6">
        <h3 class="font-bold text-gray-800 mb-3">Last 30 Days</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($last30['impressions']) }}</div>
                <div class="text-xs text-gray-500 uppercase">Impressions</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ number_format($last30['clicks']) }}</div>
                <div class="text-xs text-gray-500 uppercase">Clicks</div>
            </div>
        </div>
    </div>

    {{-- Info --}}
    <div class="bg-gray-50 rounded-xl border p-5 text-sm text-gray-600">
        <div class="grid grid-cols-2 gap-3">
            <div><span class="text-gray-500">Status:</span> <strong>{{ ucfirst(str_replace('_', ' ', $ad->status)) }}</strong></div>
            <div><span class="text-gray-500">Duration:</span> <strong>{{ $ad->duration_days }} days</strong></div>
            @if($ad->start_date)
                <div><span class="text-gray-500">Started:</span> {{ $ad->start_date->format('M d, Y') }}</div>
            @endif
            @if($ad->end_date)
                <div><span class="text-gray-500">Expires:</span> {{ $ad->end_date->format('M d, Y') }}</div>
            @endif
        </div>
    </div>

</div>
@endsection