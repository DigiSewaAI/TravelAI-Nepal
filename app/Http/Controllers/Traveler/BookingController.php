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
            'service.provider.paymentMethods',
            'review',
            'qrScans'
        ]);

        return view('traveler.bookings.show', compact('booking'));
    }

    /**
     * PHASE 7E.2 — "I've Paid" notification.
     * Sets payment_notice_sent_at. NO payment record created.
     */
    public function notifyPayment(Booking $booking)
    {
        if ($booking->traveler_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        if ($booking->payment_notice_sent_at !== null) {
            return back()->with('info', __('messages.traveler_payment_already_notified'));
        }

        $booking->update(['payment_notice_sent_at' => now()]);

        return back()->with('success', __('messages.traveler_payment_notified_success'));
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