<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\TransportBooking;
use App\Models\ProviderPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
}