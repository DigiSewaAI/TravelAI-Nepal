@extends('layouts.admin')

@section('title', 'Payment Reports | TravelAI Nepal')
@section('header', 'Payment Reports')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <form method="GET" class="flex flex-wrap gap-4 mb-6">
        <div>
            <label class="block text-sm text-gray-500">From Date</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                   class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm text-gray-500">To Date</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                   class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.reports.payments') }}" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">
                Reset
            </a>
        </div>
    </form>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-2xl font-bold text-green-600">Rs. {{ number_format($totalAmount, 2) }}</p>
            <p class="text-xs text-gray-500">Total Revenue</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $totalCount }}</p>
            <p class="text-xs text-gray-500">Total Transactions</p>
        </div>
    </div>

    @if($payments->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Date</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Provider</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Amount</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Gateway</th>
                        <th class="text-left py-2 text-sm font-semibold text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 text-sm">{{ $payment->created_at->format('Y-m-d') }}</td>
                        <td class="py-2 text-sm">{{ $payment->provider->name ?? 'N/A' }}</td>
                        <td class="py-2 text-sm font-semibold">Rs. {{ number_format($payment->amount, 2) }}</td>
                        <td class="py-2 text-sm">{{ strtoupper($payment->gateway) }}</td>
                        <td class="py-2 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Success</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No payments in this date range.</p>
    @endif
</div>
@endsection