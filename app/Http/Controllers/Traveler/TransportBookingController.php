<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\TransportBooking;
use App\Models\ProviderPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\TransportCancellationRequest;

class TransportBookingController extends Controller
{
    private function travelerEmail(): string
    {
        return Auth::user()->email;
    }

    public function index()
    {
        $bookings = TransportBooking::with(['trip.vehicle', 'trip.route', 'provider', 'vehicle'])
            ->where('passenger_email', $this->travelerEmail())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('traveler.transport.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = TransportBooking::with(['trip.vehicle', 'trip.route.fromLocation', 'trip.route.toLocation', 'provider', 'vehicle'])
            ->where('passenger_email', $this->travelerEmail())
            ->findOrFail($id);

        $paymentMethods = collect();
        if (($booking->payment_model ?? 'pay_on_arrival') === 'advance' && in_array($booking->payment_status, ['pending'])) {
            $paymentMethods = ProviderPaymentMethod::where('provider_id', $booking->provider_id)
                ->where('is_active', true)
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->get();
        }

        return view('traveler.transport.bookings.show', compact('booking', 'paymentMethods'));
    }

    public function uploadProof(Request $request, $id)
    {
        $booking = TransportBooking::where('passenger_email', $this->travelerEmail())
            ->findOrFail($id);

        if ($booking->payment_status !== 'pending') {
            return back()->withErrors(['payment' => 'Payment proof already submitted or verified.']);
        }

        if (($booking->payment_model ?? 'pay_on_arrival') !== 'advance') {
            return back()->withErrors(['payment' => 'This booking does not require advance payment.']);
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|exists:provider_payment_methods,id',
            'payment_ref'       => 'required|string|max:100',
            'payment_proof'     => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Ownership: method must belong to booking's provider
        $method = ProviderPaymentMethod::where('id', $validated['payment_method_id'])
            ->where('provider_id', $booking->provider_id)
            ->firstOrFail();

        $proofPath = $request->file('payment_proof')->store('transport-payment-proofs/' . $booking->id, 'public');

        $booking->update([
            'payment_status'       => 'submitted',
            'payment_method'       => $method->type,
            'payment_ref'          => $validated['payment_ref'],
            'payment_proof'        => $proofPath,
            'payment_submitted_at' => now(),
        ]);

        // Notify provider
        try {
            $providerUser = $booking->provider?->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\PaymentProofSubmittedNotification($booking));
            }
        } catch (\Throwable $e) {
            \Log::warning('Payment proof notify failed', ['id' => $booking->id, 'e' => $e->getMessage()]);
        }

        return redirect()
            ->route('traveler.transport.bookings.show', $booking->id)
            ->with('success', 'Payment proof submitted. The provider will verify shortly.');
    }
        public function downloadReceipt($id)
    {
        $booking = TransportBooking::where('passenger_email', $this->travelerEmail())
            ->findOrFail($id);

        $invoice = \App\Models\Invoice::where('provider_id', $booking->provider_id)
            ->whereJsonContains('metadata->transport_booking_id', $booking->id)
            ->firstOrFail();

        $provider = $booking->provider;
        $brand = app(\App\Services\TransportReceiptService::class)->buildBrandForPdf($provider);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.transport-receipt', [
            'invoice'  => $invoice,
            'booking'  => $booking,
            'provider' => $provider,
            'brand'    => $brand,
        ]);

        return $pdf->download('receipt-' . $invoice->invoice_number . '.pdf');
    }
        public function showCancelForm($id)
    {
        $booking = TransportBooking::with(['trip.route', 'provider'])
            ->where('passenger_email', $this->travelerEmail())
            ->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->route('traveler.transport.bookings.show', $id)
                ->withErrors(['cancel' => 'This booking cannot be cancelled.']);
        }

        if ($booking->cancellation_status) {
            return redirect()->route('traveler.transport.bookings.show', $id)
                ->withErrors(['cancel' => 'Cancellation request already exists.']);
        }

        // Calculate hours until departure
        $departure = \Carbon\Carbon::parse($booking->travel_date->format('Y-m-d') . ' ' . ($booking->travel_time ?? '00:00:00'));
        $hoursUntil = max(0, now()->diffInHours($departure, false));

        // Refund calculation
        $refundPct = 0;
        if (($booking->payment_status ?? 'pending') === 'paid') {
            if ($hoursUntil >= 24) $refundPct = 1.0;
            elseif ($hoursUntil >= 12) $refundPct = 0.5;
        }
        $estimatedRefund = $refundPct * (float) ($booking->total_amount ?? $booking->amount ?? 0);

        // Policy display
        $policy = $booking->provider?->services()->first()?->transportDetail?->cancellation_policy
            ?? "Full refund (100%) if cancelled 24+ hours before departure.\nPartial refund (50%) if cancelled 12-24 hours before departure.\nNo refund if cancelled less than 12 hours before departure.\nPay on Arrival bookings require no refund.";

        return view('traveler.transport.bookings.cancel-request', compact('booking', 'hoursUntil', 'estimatedRefund', 'policy'));
    }

    public function requestCancel(Request $request, $id)
    {
        $booking = TransportBooking::where('passenger_email', $this->travelerEmail())
            ->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['cancel' => 'This booking cannot be cancelled.']);
        }

        if ($booking->cancellation_status) {
            return back()->withErrors(['cancel' => 'Cancellation request already exists.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $departure = \Carbon\Carbon::parse($booking->travel_date->format('Y-m-d') . ' ' . ($booking->travel_time ?? '00:00:00'));
        $hoursUntil = max(0, now()->diffInHours($departure, false));

        $refundPct = 0;
        if (($booking->payment_status ?? 'pending') === 'paid') {
            if ($hoursUntil >= 24) $refundPct = 1.0;
            elseif ($hoursUntil >= 12) $refundPct = 0.5;
        }
        $estimatedRefund = $refundPct * (float) ($booking->total_amount ?? $booking->amount ?? 0);

        \DB::transaction(function () use ($booking, $validated, $estimatedRefund) {
            TransportCancellationRequest::create([
                'transport_booking_id' => $booking->id,
                'requested_by'         => Auth::id(),
                'reason'               => $validated['reason'],
                'estimated_refund'     => $estimatedRefund,
                'status'               => 'pending',
                'refund_status'        => 'none',
            ]);

            $booking->update(['cancellation_status' => 'pending']);

            try {
                $providerUser = $booking->provider?->user;
                if ($providerUser) {
                    $providerUser->notify(new \App\Notifications\CancellationRequestedNotification($booking));
                }
            } catch (\Throwable $e) {
                \Log::warning('Cancel request notify failed', ['id' => $booking->id]);
            }
        });

        return redirect()->route('traveler.transport.bookings.show', $id)
            ->with('success', 'Cancellation request submitted. The provider will respond shortly.');
    }
}