<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #{{ $booking->id }} | TravelAI Nepal Agency</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h1 class="text-2xl font-bold text-white">Booking Details</h1>
                <p class="text-blue-100">ID: #{{ $booking->id }}</p>
            </div>
            <div class="p-6">
                <!-- Trek Information -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Trek / Tour Details</h2>
                <div class="grid grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium">{{ $booking->trek->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Duration</p>
                        <p class="font-medium">{{ $booking->trek->duration_days ?? '-' }} days</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Difficulty</p>
                        <p class="font-medium capitalize">{{ $booking->trek->difficulty ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Price</p>
                        <p class="font-medium">${{ number_format($booking->trek->price ?? 0, 2) }}</p>
                    </div>
                </div>

                <!-- Trekker Information -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Trekker Details</h2>
                <div class="grid grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500">Full Name</p>
                        <p class="font-medium">{{ $booking->trekker->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $booking->trekker->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium">{{ $booking->trekker->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Passport Number</p>
                        <p class="font-medium">{{ $booking->trekker->passport_number ?? '-' }}</p>
                    </div>
                </div>

                <!-- Booking Meta -->
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Booking Meta</h2>
                <div class="grid grid-cols-2 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500">Booking Date</p>
                        <p class="font-medium">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Start Date</p>
                        <p class="font-medium">{{ $booking->start_date ? \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($booking->status == 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status == 'cancelled') bg-red-100 text-red-800
                                @elseif($booking->status == 'completed') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">QR Code</p>
                        <p class="font-mono text-xs">{{ $booking->qr_code ?? 'Not generated' }}</p>
                    </div>
                </div>

                <!-- QR Code Image (optional) -->
                @if($booking->qr_code)
                <div class="mb-6 text-center">
                    <p class="text-sm text-gray-500 mb-2">Check-in QR Code</p>
                    <img src="{{ route('booking.qr', $booking->id) }}" alt="QR Code" class="mx-auto border p-2 rounded-lg w-40 h-40">
                </div>
                @endif

                <div class="flex justify-between items-center mt-6 pt-4 border-t">
                    <a href="{{ route('agency.bookings.index') }}" class="text-blue-600 hover:underline">← Back to Bookings</a>
                    @if($booking->status == 'pending')
                    <form method="POST" action="{{ route('agency.bookings.updateStatus', $booking) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Confirm Booking</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>