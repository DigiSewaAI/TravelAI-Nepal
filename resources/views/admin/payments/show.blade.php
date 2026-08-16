@extends('layouts.admin')

@section('title', 'Payment Detail')

@section('header', 'Payment Detail')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Payment #{{ $payment->id }}</h2>
            <p class="text-gray-500 text-sm">Created: {{ $payment->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-sm font-medium
                {{ $payment->status === 'success' ? 'bg-green-100 text-green-700' : '' }}
                {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $payment->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                {{ $payment->status === 'refunded' ? 'bg-gray-100 text-gray-700' : '' }}">
                {{ ucfirst($payment->status) }}
            </span>
        </div>
    </div>

    @php
        // Get display currency from session
        $displayCurrency = session('display_currency', 'NPR');
        // Get exchange rate (default 152.60 if not set)
        $exchangeRate = session('exchange_rate', config('app.exchange_rate', 152.60));
        // Convert amount if needed
        $convertedAmount = $payment->amount;
        if ($displayCurrency !== $payment->currency) {
            if ($payment->currency === 'USD' && $displayCurrency === 'NPR') {
                $convertedAmount = $payment->amount * $exchangeRate;
            } elseif ($payment->currency === 'NPR' && $displayCurrency === 'USD') {
                $convertedAmount = $payment->amount / $exchangeRate;
            }
            // Add other currency pairs if needed
        }
        $symbol = $displayCurrency === 'NPR' ? 'Rs.' : '$';
    @endphp

    <div class="grid grid-cols-2 gap-6">
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Payment Details</h3>
            <div class="space-y-2">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Payment ID</span>
                    <span class="text-gray-800 font-mono text-sm">{{ $payment->payment_id }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Gateway</span>
                    <span class="text-gray-800">{{ $payment->gateway }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Amount ({{ $displayCurrency }})</span>
                    <span class="text-gray-800 font-semibold">
                        {{ $symbol }} {{ number_format($convertedAmount, 2) }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Original Currency</span>
                    <span class="text-gray-500 text-sm">{{ $payment->currency }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Provider</span>
                    <span class="text-gray-800">{{ $payment->provider->name }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">User</span>
                    <span class="text-gray-800">{{ $payment->user?->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Timeline</h3>
            <div class="space-y-2">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Created</span>
                    <span class="text-gray-800">{{ $payment->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-500">Paid At</span>
                    <span class="text-gray-800">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y H:i') : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Metadata --}}
    @if($payment->metadata)
    <div class="border-t mt-6 pt-4">
        <h3 class="font-semibold text-gray-700 mb-2">Metadata</h3>
        <div class="bg-gray-50 p-3 rounded-lg">
            @php
                $metadata = is_string($payment->metadata) ? json_decode($payment->metadata, true) : $payment->metadata;
            @endphp
            @if(is_array($metadata) && count($metadata) > 0)
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    @foreach($metadata as $key => $value)
                        <dt class="text-gray-500 font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="text-gray-800">
                            @if(is_array($value))
                                {{ json_encode($value) }}
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    @endforeach
                </dl>
            @else
                <p class="text-gray-500 text-sm">{{ $metadata ?? 'N/A' }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.payments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">← Back</a>
        @if($payment->status === 'success')
        <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="inline" onsubmit="return confirm('Are you sure you want to refund this payment?')">
            @csrf
            @method('POST')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">Refund Payment</button>
        </form>
        @endif
    </div>
</div>
@endsection