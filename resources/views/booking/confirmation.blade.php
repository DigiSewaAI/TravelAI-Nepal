<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | TravelAI Nepal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden text-center">
            <div class="bg-green-500 px-6 py-4">
                <i class="fas fa-check-circle text-white text-5xl"></i>
                <h1 class="text-2xl font-bold text-white mt-2">Booking Confirmed!</h1>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">Thank you <strong>{{ $booking->trekker->name }}</strong>. Your booking for <strong>{{ $booking->trek->name }}</strong> has been received.</p>
                <div class="bg-gray-100 p-4 rounded-lg mb-4">
                    <p class="text-sm text-gray-600">Booking ID: #{{ $booking->id }}</p>
                    <p class="text-sm text-gray-600">Start Date: {{ $booking->start_date->format('Y-m-d') }}</p>
                    <p class="text-sm text-gray-600">Status: <span class="font-semibold text-yellow-600">Pending</span> (agency will confirm soon)</p>
                </div>
                <div class="border-t pt-4">
                    <p class="font-semibold text-gray-800 mb-2">Your QR Code (for check-in)</p>
                    <div class="bg-white p-4 inline-block rounded-lg shadow">
                        {!! QrCode::size(200)->generate(route('scan.checkin', $booking->id)) !!}
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Save this QR or screenshot. You'll need it for checkpoints.</p>
                </div>
                <a href="/" class="inline-block mt-6 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>