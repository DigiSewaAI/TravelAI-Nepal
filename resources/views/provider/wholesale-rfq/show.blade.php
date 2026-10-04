@extends('layouts.provider')

@section('title', $rfq->rfq_number)
@section('header', $rfq->rfq_number)

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $rfq->rfq_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $rfq->created_at->format('M d, Y H:i') }}</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-sm font-semibold
            @if($rfq->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($rfq->status === 'quoted') bg-blue-100 text-blue-800
            @elseif($rfq->status === 'accepted') bg-green-100 text-green-800
            @else bg-gray-100 text-gray-800
            @endif">
            {{ ucfirst($rfq->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-semibold text-sm mb-2">{{ __('messages.provider_order_customer') }}</h3>
            <p class="text-sm">{{ $rfq->buyer->name ?? 'N/A' }}</p>
            @if($rfq->buyer_company)
                <p class="text-sm text-gray-600">{{ $rfq->buyer_company }}</p>
            @endif
            @if($rfq->buyer_phone)
                <p class="text-sm text-gray-600">{{ $rfq->buyer_phone }}</p>
            @endif
            <p class="text-sm text-gray-500">{{ $rfq->buyer->email ?? '' }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-semibold text-sm mb-2">Product</h3>
            <p class="text-sm">{{ $rfq->product->name ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600">{{ __('messages.rfq_quantity') }}: {{ $rfq->quantity }}</p>
        </div>
    </div>

    @if($rfq->message)
        <div class="bg-white border rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-sm mb-2">{{ __('messages.rfq_message') }}</h3>
            <p class="text-sm whitespace-pre-line">{{ $rfq->message }}</p>
        </div>
    @endif

    @if($rfq->isPending())
        <div class="border-t pt-4 mb-6">
            <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.provider_rfq_respond') }}</h3>
            <form method="POST" action="{{ route('provider.wholesale-rfq.quote', $rfq) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_quote_price') }} (NPR/unit) *</label>
                        <input type="number" name="quoted_price" step="0.01" min="0" required
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_valid_until') }}</label>
                        <input type="date" name="valid_until" min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                               class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_message') }}</label>
                    <textarea name="provider_response" rows="3" maxlength="2000"
                              class="w-full border rounded-lg px-3 py-2"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">
                    {{ __('messages.provider_rfq_send_quote') }}
                </button>
            </form>
        </div>
    @elseif($rfq->isQuoted())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-gray-600">{{ __('messages.rfq_quote_price') }}:
                <strong>NPR {{ number_format($rfq->quoted_price, 2) }}</strong></p>
            <p class="text-sm text-gray-600">{{ __('messages.rfq_quote_total') }}:
                <strong>NPR {{ number_format($rfq->quoted_total, 2) }}</strong></p>
            @if($rfq->valid_until)
                <p class="text-sm text-gray-600">{{ __('messages.rfq_valid_until') }}:
                    <strong>{{ $rfq->valid_until->format('M d, Y') }}</strong></p>
            @endif
        </div>
    @endif

    {{-- Messages --}}
    <div class="border-t pt-4">
        <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.rfq_message') }}</h3>
        <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
            @forelse($rfq->messages as $msg)
                <div class="p-3 rounded-lg {{ $msg->sender_role === 'provider' ? 'bg-blue-50 ml-8' : 'bg-gray-100 mr-8' }}">
                    <p class="text-xs text-gray-500 mb-1">
                        {{ $msg->sender->name ?? 'N/A' }}
                        ({{ $msg->sender_role }})
                        · {{ $msg->created_at->diffForHumans() }}
                    </p>
                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $msg->message }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No messages yet.</p>
            @endforelse
        </div>

        @if(!in_array($rfq->status, ['expired', 'cancelled', 'rejected', 'accepted']))
            <form method="POST" action="{{ route('provider.wholesale-rfq.message', $rfq) }}" class="space-y-2">
                @csrf
                <textarea name="message" required rows="2" maxlength="2000"
                          placeholder="{{ __('messages.rfq_message') }}"
                          class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    Send
                </button>
            </form>
        @endif
    </div>

    <div class="mt-6 text-sm">
        <a href="{{ route('provider.wholesale-rfq.index') }}" class="text-gray-500 hover:text-blue-600">
            ← {{ __('messages.provider_rfq_title') }}
        </a>
    </div>

</div>
@endsection