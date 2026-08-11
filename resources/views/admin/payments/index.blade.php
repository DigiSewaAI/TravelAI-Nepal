@extends('layouts.admin')

@section('title', 'Payments | TravelAI Nepal')
@section('header', 'Manage Payments')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">All Payments</h2>

    @if($payments->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Payment ID</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Provider</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Amount</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Gateway</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Date</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr class="border-b hover:bg-gray-50">
                        <!-- ✅ Full Payment ID -->
                        <td class="py-3 text-sm font-mono">{{ $payment->payment_id }}</td>
                        <td class="py-3 text-sm">{{ $payment->provider->name ?? 'N/A' }}</td>
                        <td class="py-3 text-sm font-semibold">Rs. {{ number_format($payment->amount, 2) }}</td>
                        <td class="py-3 text-sm">{{ strtoupper($payment->gateway) }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($payment->status === 'success') bg-green-100 text-green-800
                                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($payment->status === 'refunded') bg-purple-100 text-purple-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-3 text-sm">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($payment->status === 'success')
                            <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-purple-600 hover:text-purple-800 mr-2" title="Refund">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" class="inline"
                                  onsubmit="return confirm('Delete this payment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">No payments found.</p>
    @endif
</div>
@endsection