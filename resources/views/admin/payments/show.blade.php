@extends('layouts.admin')

@section('title', 'Payment Details | TravelAI Nepal')
@section('header', 'Payment Detail')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('admin.payments.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>
    </div>

    <div class="flex justify-between items-start">
        <h2 class="text-2xl font-bold text-gray-900">Payment #{{ $payment->id }}</h2>
        <span class="px-3 py-1 rounded-full text-sm font-semibold
            @if($payment->status === 'success') bg-green-100 text-green-800
            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($payment->status === 'refunded') bg-purple-100 text-purple-800
            @else bg-red-100 text-red-800 @endif">
            {{ ucfirst($payment->status) }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4 border-t pt-4">
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
        <div>
            <p class="text-sm text-gray-500">Created</p>
            <p class="font-medium">{{ $payment->created_at->format('M d, Y H:i') }}</p>
        </div>
        @if($payment->paid_at)
        <div>
            <p class="text-sm text-gray-500">Paid At</p>
            <p class="font-medium">{{ $payment->paid_at->format('M d, Y H:i') }}</p>
        </div>
        @endif
    </div>

    @if($payment->metadata)
        <div class="mt-4 border-t pt-4">
            <h3 class="font-semibold text-gray-700 text-sm">Metadata</h3>
            <div class="bg-gray-50 p-4 rounded-lg mt-2 space-y-1 text-sm">
                @php
                    $meta = is_array($payment->metadata) ? $payment->metadata : json_decode($payment->metadata, true);
                @endphp
                @if(is_array($meta))
                    @foreach($meta as $key => $value)
                        <div class="flex justify-between border-b border-gray-100 pb-1">
                            <span class="text-gray-600 font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                            <span class="text-gray-800">{{ is_array($value) ? json_encode($value) : $value }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500">{{ $payment->metadata }}</p>
                @endif
            </div>
        </div>
    @endif

    <div class="mt-6 flex gap-3">
        @if($payment->status === 'success')
        <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="inline">
            @csrf
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-undo mr-1"></i> Refund Payment
            </button>
        </form>
        @endif
        <a href="{{ route('admin.payments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@endsection