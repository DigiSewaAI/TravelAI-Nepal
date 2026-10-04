@extends('layouts.public')

@section('title', __('messages.rfq_create_title') . ' | ' . __('messages.app_name'))

@section('content')
<section class="max-w-3xl mx-auto px-6 md:px-10 py-8">

    <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('messages.rfq_create_title') }}</h1>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-gray-50 rounded-xl p-5 mb-6">
        <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
        <p class="text-sm text-gray-500 mt-1">
            {{ __('messages.products_min_order') }}: {{ $product->wholesaleDetail->min_order_qty ?? 1 }}
        </p>
    </div>

    <form method="POST" action="{{ route('wholesale.rfq.store', $product) }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_quantity') }} *</label>
            <input type="number" name="quantity" required
                   min="{{ $product->wholesaleDetail->min_order_qty ?? 1 }}"
                   value="{{ old('quantity', $product->wholesaleDetail->min_order_qty ?? 1) }}"
                   class="w-full border rounded-lg px-3 py-2">
            @error('quantity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_buyer_company') }}</label>
            <input type="text" name="buyer_company" maxlength="150" value="{{ old('buyer_company') }}"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_buyer_phone') }}</label>
            <input type="text" name="buyer_phone" maxlength="20" value="{{ old('buyer_phone') }}"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.rfq_message') }}</label>
            <textarea name="message" rows="4" maxlength="2000"
                      class="w-full border rounded-lg px-3 py-2">{{ old('message') }}</textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('public.wholesale.index') }}" class="px-4 py-2 border rounded-lg text-sm">
                {{ __('messages.cancel') ?? 'Cancel' }}
            </a>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                {{ __('messages.rfq_submit') }}
            </button>
        </div>
    </form>

</section>
@endsection