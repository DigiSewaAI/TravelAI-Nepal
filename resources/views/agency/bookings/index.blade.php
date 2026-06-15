@extends('agency.layouts.app')

@section('title', 'Bookings')
@section('header', 'All Bookings')

@section('content')
<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trekker</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trek</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">QR Code</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($bookings as $booking)
            <tr>
                <td class="px-6 py-4">{{ $booking->trekker->name ?? 'N/A' }}</td>
                <td class="px-6 py-4">{{ $booking->trek->name }}</td>
                <td class="px-6 py-4">{{ $booking->start_date->format('Y-m-d') }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full 
                        @if($booking->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($booking->status == 'confirmed') bg-blue-100 text-blue-800
                        @elseif($booking->status == 'completed') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <button onclick="openQRModal('{{ route('booking.qr', $booking->id) }}')" class="text-blue-600 hover:underline">Show QR</button>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('scan.checkin', $booking->id) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs inline-block">Check‑in</a>
                    <a href="{{ route('agency.bookings.show', $booking) }}" class="text-blue-600 hover:underline">Details</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No bookings yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full mx-4 p-6 text-center">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800">QR Code for Check‑in</h3>
            <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="flex justify-center mb-4">
            <img id="qrImage" src="" alt="QR Code" class="border rounded-lg p-2 bg-white">
        </div>
        <p class="text-sm text-gray-500 mb-4">Scan this code at checkpoints.</p>
        <button onclick="closeQRModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Close</button>
    </div>
</div>

<script>
    function openQRModal(url) {
        document.getElementById('qrImage').src = url;
        document.getElementById('qrModal').classList.remove('hidden');
        document.getElementById('qrModal').classList.add('flex');
    }
    function closeQRModal() {
        document.getElementById('qrModal').classList.add('hidden');
        document.getElementById('qrModal').classList.remove('flex');
    }
    // Close modal when clicking outside the white box
    document.getElementById('qrModal').addEventListener('click', function(e) {
        if (e.target === this) closeQRModal();
    });
</script>
@endsection