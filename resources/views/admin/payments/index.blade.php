@extends('layouts.admin')

@section('title', 'Manage Payments')

@section('header', 'Manage Payments')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">All Payments</h2>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Payment ID</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Provider</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Amount</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Gateway</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
                    <th class="text-left py-3 px-4 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                @php
                    // 🔥 Conversion Logic (Detail Page जस्तै)
                    $displayCurrency = session('display_currency', 'NPR');
                    $exchangeRate = session('exchange_rate', config('app.exchange_rate', 152.60));
                    $convertedAmount = $payment->amount;
                    if ($displayCurrency !== $payment->currency) {
                        if ($payment->currency === 'USD' && $displayCurrency === 'NPR') {
                            $convertedAmount = $payment->amount * $exchangeRate;
                        } elseif ($payment->currency === 'NPR' && $displayCurrency === 'USD') {
                            $convertedAmount = $payment->amount / $exchangeRate;
                        }
                    }
                    $symbol = $displayCurrency === 'NPR' ? 'Rs.' : '$';
                @endphp
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 font-mono text-xs">{{ $payment->payment_id }}</td>
                    <td class="py-3 px-4">{{ $payment->provider->name ?? 'N/A' }}</td>
                    <td class="py-3 px-4 font-semibold">
                        {{ $symbol }} {{ number_format($convertedAmount, 2) }}
                    </td>
                    <td class="py-3 px-4">{{ $payment->gateway }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $payment->status === 'success' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $payment->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $payment->status === 'refunded' ? 'bg-gray-100 text-gray-700' : '' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">{{ $payment->created_at->format('Y-m-d') }}</td>
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="text-blue-600 hover:underline text-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-500">No payments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>
@endsection