@extends('layouts.provider')

@section('title', __('messages.invoice_detail_title', ['number' => $invoice->invoice_number]))
@section('header', __('messages.invoice_detail_header'))

@section('content')
<div class="bg-white rounded-lg shadow-sm border p-6 max-w-3xl">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ __('messages.invoice_hash', ['number' => $invoice->invoice_number]) }}</h2>
            <p class="text-sm text-gray-500">{{ __('messages.created_at') }}: {{ $invoice->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-sm font-medium
                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                {{ $invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
            ">
                @if($invoice->status === 'paid') {{ __('messages.paid') }}
                @elseif($invoice->status === 'pending') {{ __('messages.pending') }}
                @elseif($invoice->status === 'overdue') {{ __('messages.overdue') }}
                @else {{ ucfirst($invoice->status) }} @endif
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6 border-t border-b py-4 my-4">
        <div>
            <p class="text-sm text-gray-500">{{ __('messages.provider') }}</p>
            <p class="font-medium">{{ $invoice->provider->name ?? __('messages.na') }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ __('messages.total_amount') }}</p>
            <p class="text-2xl font-bold text-blue-600">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-gray-500">{{ __('messages.invoice_number') }}</p>
            <p class="font-medium">{{ $invoice->invoice_number }}</p>
        </div>
        <div>
            <p class="text-gray-500">{{ __('messages.receipt_number') }}</p>
            <p class="font-medium">{{ $invoice->receipt_number ?? __('messages.na') }}</p>
        </div>
        <div>
            <p class="text-gray-500">{{ __('messages.payment_method') }}</p>
            <p class="font-medium">{{ ucfirst($invoice->payment_method ?? __('messages.na')) }}</p>
        </div>
        <div>
            <p class="text-gray-500">{{ __('messages.paid_at') }}</p>
            <p class="font-medium">{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y H:i') : __('messages.na') }}</p>
        </div>
    </div>

    <div class="flex gap-3 mt-6 border-t pt-4">
        <a href="{{ route('provider.invoices.download', $invoice) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">{{ __('messages.download_pdf') }}</a>
        <a href="{{ route('provider.invoices.index') }}" class="text-gray-600 hover:text-gray-800 text-sm py-2">{{ __('messages.back_to_invoices') }}</a>
    </div>
</div>
@endsection