@extends('agency.layouts.app')

@section('title', 'Booking Details')
@section('header', 'Booking #' . $booking->id)

@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-3xl">
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <p class="text-gray-500 text-sm">Trekker</p>
            <p class="font-medium">{{ $booking->trekker->name }}</p>
            <p class="text-gray-600">{{ $booking->trekker->email }}<br>{{ $booking->trekker->phone }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Trek Details</p>
            <p class="font-medium">{{ $booking->trek->name }}</p>
            <p>{{ $booking->trek->duration_days }} days | {{ ucfirst($booking->trek->difficulty) }} | ${{ $booking->trek->price }}</p>
        </div>
    </div>

    <div class="border-t pt-4 mb-4">
        <p><strong>Start Date:</strong> {{ $booking->start_date->format('Y-m-d') }}</p>
        <p><strong>Booking Date:</strong> {{ $booking->booking_date->format('Y-m-d') }}</p>
        <p><strong>Status:</strong> 
            <form action="{{ route('agency.bookings.updateStatus', $booking) }}" method="POST" class="inline">
    @csrf
    @method('PATCH')
                <select name="status" class="border rounded px-2 py-1 text-sm" onchange="this.form.submit()">
                    <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </form>
        </p>
    </div>

    <div class="border-t pt-4">
        <p class="font-semibold mb-2">QR Code for Check-in</p>
        <div class="bg-gray-100 p-4 rounded-lg text-center">
            <p class="font-mono text-sm break-all">{{ $booking->qr_code }}</p>
<a href="{{ route('scan.checkin', $booking->id) }}" target="_blank" class="inline-block mt-2 bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700">Simulate Scan</a>        </div>
    </div>

    @if($scans->count())
    <div class="border-t pt-4 mt-4">
        <p class="font-semibold mb-2">Check-in History</p>
        <ul class="list-disc list-inside text-sm">
            @foreach($scans as $scan)
            <li>{{ $scan->checkpoint_name }} – {{ $scan->scanned_at->format('Y-m-d H:i') }} ({{ $scan->latitude }}, {{ $scan->longitude }})</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('agency.bookings.index') }}" class="text-blue-600 hover:underline">← Back to Bookings</a>
    </div>
</div>
@endsection