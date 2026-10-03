<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Mail\PaymentNoticeReceivedMail;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    /**
     * TRAVELER-ROUTES-MISSING-01 — All bookings index
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'all');

        $baseQuery = Booking::where('traveler_id', $user->id);

        // Filter
        $query = clone $baseQuery;
        switch ($filter) {
            case 'upcoming':
                $query->where('status', 'pending');
                break;
            case 'active':
                $query->where('status', 'confirmed');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            // 'all' → no filter
        }

        $bookings = $query->with(['service', 'review'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Counts for filter tabs
        $counts = [
            'all'       => (clone $baseQuery)->count(),
            'upcoming'  => (clone $baseQuery)->where('status', 'pending')->count(),
            'active'    => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
        ];

        return view('traveler.bookings.index', compact('bookings', 'filter', 'counts'));
    }

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

        // PHASE 7G — Notify provider (non-blocking)
        $booking->load('service.provider', 'traveler');
        $provider = $booking->service?->provider;
        $recipient = $provider?->contact_email;

        if ($recipient) {
            try {
                Mail::to($recipient)->send(new PaymentNoticeReceivedMail($booking));
            } catch (\Throwable $e) {
                Log::error('Booking notice email failed', [
                    'booking_id'  => $booking->id,
                    'provider_id' => $provider->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('Booking notice — no provider email', [
                'booking_id'  => $booking->id,
                'provider_id' => $provider?->id,
            ]);
        }

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
        $filename = 'booking-invoice-' . ($booking->booking_number ?? $booking->id) . '.pdf';
        return $pdf->download($filename);
    }
}