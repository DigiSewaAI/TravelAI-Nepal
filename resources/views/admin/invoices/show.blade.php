@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('header', 'Invoice Details')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $invoice->invoice_number }}</h2>
            <p class="text-gray-500 text-sm">Created: {{ $invoice->created_at->format('M d, Y H:i') }}</p>
        </div>
        <div>
            <span class="px-3 py-1 rounded-full text-sm font-medium
                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                {{ $invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                {{ $invoice->status === 'cancelled' ? 'bg-gray-100 text-gray-700' : '' }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>
    </div>

    {{-- ====== INVOICE INFO ====== --}}
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Provider</h3>
            <p class="text-gray-600">{{ $invoice->provider->name }}</p>
            <p class="text-gray-500 text-sm">{{ $invoice->provider->contact_email }}</p>
        </div>
        <div>
            <h3 class="font-semibold text-gray-700 mb-2">Payment Method</h3>
            <p class="text-gray-600">{{ $invoice->payment_method ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- ====== DESCRIPTION (New) ====== --}}
    <div class="border-t pt-4">
        <h3 class="font-semibold text-gray-700 mb-2">Description</h3>
        <div class="bg-gray-50 p-3 rounded-lg mb-4">
            @if($invoice->booking)
                <p class="text-gray-800">
                    <strong>Booking:</strong> {{ $invoice->booking->service->name ?? 'Service' }}
                </p>
                <p class="text-gray-500 text-sm">
                    Traveler: {{ $invoice->booking->traveler->name ?? 'N/A' }}
                    ({{ $invoice->booking->traveler->email ?? 'N/A' }})
                </p>
            @elseif($invoice->subscription)
                <p class="text-gray-800">
                    <strong>Subscription:</strong> {{ $invoice->subscription->plan->name ?? 'Plan' }}
                    ({{ $invoice->subscription->billing_interval ?? 'Monthly' }})
                </p>
            @else
                <p class="text-gray-800">Payment</p>
            @endif
        </div>
    </div>

    {{-- ====== AMOUNT DETAILS ====== --}}
    <div class="border-t pt-4">
        <h3 class="font-semibold text-gray-700 mb-2">Amount Details</h3>
        <div class="space-y-2">
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-500">Amount</span>
                <span class="text-gray-800">{{ $invoice->currency }} {{ number_format($invoice->amount, 2) }}</span>
            </div>
            @if($invoice->tax > 0)
            <div class="flex justify-between py-2 border-b">
                <span class="text-gray-500">Tax</span>
                <span class="text-gray-800">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between py-2 border-b font-bold text-lg">
                <span class="text-gray-800">Total</span>
                <span class="text-blue-600">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ====== METADATA (Readable Format) ====== --}}
    @if($invoice->metadata)
    <div class="border-t pt-4 mt-4">
        <h3 class="font-semibold text-gray-700 mb-2">Additional Information</h3>
        <div class="bg-gray-50 p-3 rounded-lg">
            @php
                $metadata = is_string($invoice->metadata) ? json_decode($invoice->metadata, true) : $invoice->metadata;
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

    {{-- ====== ACTIONS ====== --}}
    <div class="mt-6 flex gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition">← Back</a>
        <a href="{{ route('admin.invoices.download', $invoice) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">📄 Download PDF</a>

        @if($invoice->status !== 'paid')
        <form method="POST" action="{{ route('admin.invoices.update-status', $invoice) }}" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                Mark as Paid
            </button>
        </form>
        @endif
    </div>
</div>
@endsection