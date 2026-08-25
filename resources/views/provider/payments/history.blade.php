@extends('layouts.provider')

@section('title', __('messages.payment_history_title'))
@section('header', __('messages.payment_history_header'))

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">{{ __('messages.all_payments') }}</h2>
        <a href="{{ route('provider.subscriptions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_subscription') }}
        </a>
    </div>

    @if($payments->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.date') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.payment_id') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.plan') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.amount') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.status') }}</th>
                        <th class="text-left py-3 text-sm font-semibold text-gray-600">{{ __('messages.gateway') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 text-sm">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-3 text-sm font-mono text-gray-600">
                            <a href="{{ route('provider.payments.show', $payment->id) }}" class="text-blue-600 hover:underline">
                                {{ substr($payment->payment_id, 0, 12) }}...
                            </a>
                        </td>
                        <td class="py-3 text-sm">{{ $payment->payable->plan->name ?? __('messages.na') }}</td>
                        <td class="py-3 text-sm font-semibold">Rs. {{ number_format($payment->amount, 2) }}</td>
                        <td class="py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs
                                @if($payment->status === 'success') bg-green-100 text-green-800
                                @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($payment->status === 'success') {{ __('messages.success') }}
                                @elseif($payment->status === 'failed') {{ __('messages.failed') }}
                                @elseif($payment->status === 'pending') {{ __('messages.pending') }}
                                @else {{ ucfirst($payment->status) }} @endif
                            </span>
                        </td>
                        <td class="py-3 text-sm">{{ strtoupper($payment->gateway) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center py-8">{{ __('messages.no_payments_yet') }}</p>
    @endif
</div>
@endsection