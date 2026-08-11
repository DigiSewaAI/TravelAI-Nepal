@extends('layouts.admin')

@section('title', 'Subscription Details | TravelAI Nepal')
@section('header', 'Subscription Detail')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border p-6">
    <div class="mb-4">
        <a href="{{ route('admin.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left"></i> Back to Subscriptions
        </a>
    </div>

    <h2 class="text-2xl font-bold text-gray-900">Subscription #{{ $subscription->id }}</h2>
    <div class="flex gap-2 mt-2">
        <span class="px-2 py-1 text-xs rounded-full
            @if($subscription->status === 'active') bg-green-100 text-green-800
            @elseif($subscription->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800
            @else bg-gray-100 text-gray-800 @endif">
            {{ ucfirst($subscription->status) }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 mt-4 border-t pt-4">
        <div>
            <p class="text-sm text-gray-500">Provider</p>
            <p class="font-medium">{{ $subscription->provider->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Plan</p>
            <p class="font-medium">{{ $subscription->plan->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Start Date</p>
            <p class="font-medium">{{ $subscription->start_date ? $subscription->start_date->format('Y-m-d') : 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">End Date</p>
            <p class="font-medium">{{ $subscription->end_date ? $subscription->end_date->format('Y-m-d') : 'N/A' }}</p>
        </div>
    </div>

    <div class="mt-6 border-t pt-4">
        <h3 class="font-semibold text-gray-700">Update Status</h3>
        <form method="POST" action="{{ route('admin.subscriptions.updateStatus', $subscription) }}" class="flex gap-2 mt-2">
            @csrf
            <select name="status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="pending" {{ $subscription->status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="active" {{ $subscription->status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="expired" {{ $subscription->status === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="cancelled" {{ $subscription->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                Update
            </button>
        </form>
    </div>

    @if($subscription->payments && $subscription->payments->count() > 0)
        <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold text-gray-700">Payments</h3>
            <div class="mt-2 space-y-2">
                @foreach($subscription->payments as $payment)
                    <div class="flex justify-between items-center border-b pb-1 text-sm">
                        <span>Rs. {{ number_format($payment->amount, 2) }}</span>
                        <span class="px-2 py-0.5 text-xs rounded-full
                            @if($payment->status === 'success') bg-green-100 text-green-800
                            @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($payment->status) }}
                        </span>
                        <span class="text-gray-400 text-xs">{{ $payment->created_at->format('Y-m-d') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.subscriptions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@endsection