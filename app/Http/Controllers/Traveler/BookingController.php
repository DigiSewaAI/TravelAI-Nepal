<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;  // ✅ PDF Facade

class BookingController extends Controller
{
    public function show(Booking $booking)
    {
        if ($booking->traveler_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $booking->load([
            'service', 
            'service.provider',
            'review',
            'qrScans'
        ]);
        
        return view('traveler.bookings.show', compact('booking'));
    }

    // 🔥 NEW: Download Invoice
    public function downloadInvoice(Booking $booking)
    {
        // Only the booking owner can download
        if ($booking->traveler_id !== Auth::id()) {
            abort(403, 'Unauthorized.');
        }

        $data = [
            'booking' => $booking,
            'service' => $booking->service,
            'provider' => $booking->service->provider,
            'traveler' => $booking->traveler,
        ];

        $pdf = Pdf::loadView('invoices.booking-pdf', $data);
        return $pdf->download('booking-invoice-' . $booking->id . '.pdf');
    }
}