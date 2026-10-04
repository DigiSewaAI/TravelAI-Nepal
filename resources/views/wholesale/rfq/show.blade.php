@extends('layouts.public')

@section('title', $rfq->rfq_number . ' | ' . __('messages.app_name'))

@section('content')
<section class="max-w-4xl mx-auto px-6 md:px-10 py-8">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <nav class="text-sm text-gray-500 mb-4">
        <a href="{{ route('wholesale.rfq.index') }}" class="hover:text-blue-600">{{ __('messages.rfq_index_title') }}</a>
        <span class="mx-2">/</span>
        <span>{{ $rfq->rfq_number }}</span>
    </nav>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $rfq->rfq_number }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $rfq->created_at->format('M d, Y H:i') }}</p>
            </div>
            <span class="inline-block px-3 py-1.5 rounded-full text-sm font-semibold
                @if($rfq->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($rfq->status === 'quoted') bg-blue-100 text-blue-800
                @elseif($rfq->status === 'accepted') bg-green-100 text-green-800
                @elseif($rfq->status === 'rejected') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($rfq->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-sm mb-2">{{ __('messages.rfq_product') ?? 'Product' }}</h3>
                <p class="text-sm">{{ $rfq->product->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('messages.rfq_quantity') }}: {{ $rfq->quantity }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-sm mb-2">{{ __('messages.provider_order_customer') }}</h3>
                <p class="text-sm">{{ $rfq->provider->name ?? 'N/A' }}</p>
            </div>
        </div>

        @if($rfq->quoted_total)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-2">{{ __('messages.rfq_provider_responded') }}</h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">{{ __('messages.rfq_quote_price') }}</p>
                        <p class="font-bold">NPR {{ number_format($rfq->quoted_price, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('messages.rfq_quote_total') }}</p>
                        <p class="font-bold text-blue-600">NPR {{ number_format($rfq->quoted_total, 2) }}</p>
                    </div>
                    @if($rfq->valid_until)
                        <div>
                            <p class="text-gray-500">{{ __('messages.rfq_valid_until') }}</p>
                            <p class="font-bold">{{ $rfq->valid_until->format('M d, Y') }}</p>
                        </div>
                    @endif
                </div>
                @if($rfq->provider_response)
                    <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $rfq->provider_response }}</p>
                @endif

                @if($rfq->isQuoted())
                    <div class="flex gap-3 mt-4">
                        <form method="POST" action="{{ route('wholesale.rfq.accept', $rfq) }}">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                                {{ __('messages.rfq_accept') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('wholesale.rfq.reject', $rfq) }}">
                            @csrf
                            <button type="submit" class="border border-red-500 text-red-500 hover:bg-red-50 px-5 py-2 rounded-lg text-sm font-medium">
                                {{ __('messages.rfq_reject') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endif

        {{-- Messages --}}
        <div class="border-t pt-4">
            <h3 class="font-semibold text-gray-900 mb-3">{{ __('messages.rfq_message') }}</h3>
            <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                @forelse($rfq->messages as $msg)
                    <div class="p-3 rounded-lg {{ $msg->sender_role === 'buyer' ? 'bg-blue-50 ml-8' : 'bg-gray-100 mr-8' }}">
                        <p class="text-xs text-gray-500 mb-1">
                            {{ $msg->sender->name ?? 'N/A' }}
                            ({{ $msg->sender_role }})
                            · {{ $msg->created_at->diffForHumans() }}
                        </p>
                        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $msg->message }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('messages.rfq_no_messages') ?? 'No messages yet.' }}</p>
                @endforelse
            </div>

            @if(!in_array($rfq->status, ['expired', 'cancelled', 'rejected', 'accepted']))
                <form method="POST" action="{{ route('wholesale.rfq.message', $rfq) }}" class="space-y-2">
                    @csrf
                    <textarea name="message" required rows="2" maxlength="2000"
                              placeholder="{{ __('messages.rfq_message') }}"
                              class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        {{ __('messages.rfq_send') ?? 'Send' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

</section>
@endsection