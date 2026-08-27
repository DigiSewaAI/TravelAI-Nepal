@extends('layouts.app')

@section('title', 'QR Code Invalid')

@section('content')
<div class="container mx-auto px-4 py-16 max-w-md">
    <div class="bg-white rounded-2xl shadow-xl p-8 text-center border border-red-100">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Invalid QR Code</h1>
        <p class="text-gray-600 mb-4">{{ $message ?? 'This QR code is invalid or has expired.' }}</p>
        <p class="text-sm text-gray-400">Booking #{{ $booking->id }}</p>
        <a href="{{ route('home') }}" class="inline-block mt-6 bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 transition">
            Go to Home
        </a>
    </div>
</div>
@endsection