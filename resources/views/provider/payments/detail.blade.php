@extends('layouts.provider')

@section('title', 'Payment Detail | TravelAI Nepal')
@section('header', 'Payment Detail')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('provider.payments.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Payment #{{ $payment->id }}</h2>
                <p class="text-sm text-gray-500">{{ $payment->created_at->format('F d, Y H:i') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                @if($payment->status === 'success') bg-green-100 text-green-800
                @elseif($payment->status === 'failed') bg-red-100 text-red-800
                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst($payment->status) }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-4 border-t pt-4">
            <div>
                <p class="text-sm text-gray-500">Payment ID</p>
                <p class="font-mono text-sm">{{ $payment->payment_id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Gateway</p>
                <p class="font-medium">{{ strtoupper($payment->gateway) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Amount</p>
                <p class="text-xl font-bold text-blue-600">Rs. {{ number_format($payment->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Currency</p>
                <p class="font-medium">{{ $payment->currency }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Provider</p>
                <p class="font-medium">{{ $payment->provider->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">User</p>
                <p class="font-medium">{{ $payment->user->name ?? 'N/A' }}</p>
            </div>
            @if($payment->paid_at)
                <div>
                    <p class="text-sm text-gray-500">Paid At</p>
                    <p class="font-medium">{{ $payment->paid_at->format('F d, Y H:i') }}</p>
                </div>
            @endif
        </div>

        @if($payment->metadata)
            <div class="border-t pt-4 mt-4">
                <h3 class="font-semibold text-gray-700 text-sm">Metadata</h3>
                <pre class="text-xs bg-gray-50 p-3 rounded-lg mt-2 overflow-x-auto">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if($payment->payable)
            <div class="border-t pt-4 mt-4">
                <h3 class="font-semibold text-gray-700 text-sm">Payable</h3>
                <p class="text-sm">Type: {{ class_basename($payment->payable_type) }}</p>
                <p class="text-sm">ID: #{{ $payment->payable_id }}</p>
            </div>
        @endif
    </div>
</div>
@endsection