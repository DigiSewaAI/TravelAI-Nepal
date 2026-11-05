@extends('layouts.traveler-v2')
@section('title', __('messages.transport_claim_title'))
@section('traveler-v2-content')
<div class="max-w-2xl mx-auto py-10 px-4">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8">
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('messages.transport_claim_heading') }}</h1>
        <p class="text-sm text-slate-600 mb-6">{{ __('messages.transport_claim_desc') }}</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl mb-6">{{ $errors->first() }}</div>
        @endif
        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl mb-6">{{ session('info') }}</div>
        @endif

        <form action="{{ route('traveler.transport.claim.initiate') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.transport_booking_ref') }}</label>
                <input type="text" name="booking_ref" value="{{ old('booking_ref') }}" required maxlength="50"
                       placeholder="TRP-XXXX..." class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-mono uppercase">
                <p class="text-xs text-slate-500 mt-1">{{ __('messages.transport_booking_ref_hint') }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.transport_passenger_email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="150"
                       placeholder="your@email.com" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl">
                {{ __('messages.transport_claim_submit') }}
            </button>
        </form>
    </div>
</div>
@endsection