@extends('layouts.public')

@section('title', __('messages.rfq_index_title') . ' | ' . __('messages.app_name'))

@section('content')
<section class="max-w-5xl mx-auto px-6 md:px-10 py-8">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('messages.rfq_index_title') }}</h1>

    @if($rfqs->isEmpty())
        <div class="text-center py-16 bg-gray-50 rounded-xl">
            <p class="text-gray-500">{{ __('messages.rfq_empty') }}</p>
            <a href="{{ route('public.wholesale.index') }}" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
                {{ __('messages.cart_continue_shopping') }}
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($rfqs as $rfq)
                <a href="{{ route('wholesale.rfq.show', $rfq) }}"
                   class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $rfq->rfq_number }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $rfq->product->name ?? 'N/A' }} × {{ $rfq->quantity }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $rfq->provider->name ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold
                                @if($rfq->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($rfq->status === 'quoted') bg-blue-100 text-blue-800
                                @elseif($rfq->status === 'accepted') bg-green-100 text-green-800
                                @elseif($rfq->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($rfq->status) }}
                            </span>
                            @if($rfq->quoted_total)
                                <p class="text-sm font-bold text-blue-600 mt-2">NPR {{ number_format($rfq->quoted_total, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $rfqs->links() }}</div>
    @endif

</section>
@endsection