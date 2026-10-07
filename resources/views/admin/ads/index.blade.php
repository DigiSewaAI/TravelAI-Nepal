@extends('layouts.admin')

@section('title', __('messages.ads_admin_menu'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-bullhorn text-amber-500"></i>
                {{ __('messages.ads_admin_menu') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.ads_admin_subtitle') }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @php
            $tabs = [
                'pending_review' => __('messages.ads_status_pending_review'),
                'active' => __('messages.ads_status_active'),
                'rejected' => __('messages.ads_status_rejected'),
                'expired' => __('messages.ads_status_expired'),
                'all' => __('messages.ads_tab_all'),
            ];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.ads.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition
                   {{ $status === $key ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-gray-700 border hover:border-amber-300' }}">
                {{ $label }}
                @if(isset($counts[$key]))
                    <span class="ml-1 text-xs opacity-75">({{ $counts[$key] }})</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        @if($ads->count() > 0)
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Ad</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Provider</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Duration</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Amount</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Status</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ads as $ad)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ Storage::url($ad->image_path) }}" class="w-16 h-10 object-cover rounded">
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 truncate max-w-xs">{{ $ad->title }}</div>
                                        <div class="text-xs text-gray-400">{{ $ad->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-800">{{ $ad->provider->name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $ad->provider->contact_email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">{{ $ad->duration_days }} days</td>
                            <td class="px-4 py-3 text-center font-bold">Rs. {{ number_format($ad->price_paid) }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $colors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'pending_review' => 'bg-amber-100 text-amber-700',
                                        'payment_pending' => 'bg-blue-100 text-blue-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        'expired' => 'bg-gray-100 text-gray-600',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $colors[$ad->status] ?? 'bg-gray-100' }}">
                                    {{ ucfirst(str_replace('_', ' ', $ad->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.ads.show', $ad) }}"
                                   class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t">
                {{ $ads->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">{{ __('messages.ads_admin_empty') }}</p>
            </div>
        @endif
    </div>

</div>
@endsection