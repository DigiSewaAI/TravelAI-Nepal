@extends('layouts.traveler-v2')
@section('title', __('messages.claim_verify_title'))
@section('traveler-v2-content')
<div class="max-w-md mx-auto py-10 px-4">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8 text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-envelope text-blue-600 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('messages.claim_verify_heading') }}</h1>
        <p class="text-sm text-slate-600 mb-6">{{ __('messages.claim_verify_sent_to') }} <strong>{{ $attempt->contact_value }}</strong></p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded-xl mb-4 text-sm">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('traveler.restaurant.claim.verify', ['token' => $attempt->id]) }}" method="POST">
            @csrf
            <input type="text" name="otp" required maxlength="6" pattern="\d{6}"
                   placeholder="000000" autofocus
                   class="w-full text-center text-3xl font-mono tracking-widest px-4 py-4 border-2 border-slate-200 rounded-xl focus:border-blue-500 mb-4">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl">
                Verify Code
            </button>
        </form>

        <p class="text-xs text-slate-400 mt-4">{{ __('messages.claim_verify_expires') }}</p>
    </div>
</div>
@endsection