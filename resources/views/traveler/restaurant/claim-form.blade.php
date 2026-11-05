@extends('layouts.traveler-v2')
@section('title', __('messages.claim_form_title'))
@section('traveler-v2-content')
<div class="max-w-2xl mx-auto py-10 px-4">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('messages.claim_form_heading') }}</h1>
        <p class="text-sm text-slate-600 mb-6">{{ __('messages.claim_form_desc') }}</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('traveler.restaurant.claim.initiate') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.claim_order_number') }}</label>
                <input type="text" name="order_number" value="{{ old('order_number') }}" required
                       placeholder="e.g. ORD-REST-00025"
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-slate-500 mt-1">{{ __('messages.claim_order_number_hint') }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.claim_verification_method') }}</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="method" value="receipt" {{ old('method', 'receipt') === 'receipt' ? 'checked' : '' }}>
                        <span class="text-sm font-medium">{{ __('messages.claim_method_receipt') }}</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50">
                        <input type="radio" name="method" value="email" {{ old('method') === 'email' ? 'checked' : '' }}>
                        <span class="text-sm font-medium">{{ __('messages.claim_method_email') }}</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.claim_receipt_code_label') }}</label>
                <input type="text" name="receipt_code" value="{{ old('receipt_code') }}"
                       maxlength="20" placeholder="e.g. ABC2XY4K"
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-mono uppercase">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.claim_email_label') }}</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       maxlength="190" placeholder="your@email.com"
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition">
                Verify & Claim Order
            </button>
        </form>

        <p class="text-xs text-slate-500 text-center mt-6">
            {{ __('messages.claim_historical_hint') }} <a href="{{ route('safety.index') }}" class="text-blue-600 hover:underline">{{ __('messages.claim_contact_restaurant') }}</a>.
        </p>
    </div>
</div>
@endsection