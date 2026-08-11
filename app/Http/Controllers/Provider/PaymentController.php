<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Payment history list.
     */
    public function history()
    {
        $provider = Auth::user()->ownProvider();
        
        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $payments = Payment::where('provider_id', $provider->id)
            ->with(['payable', 'provider'])
            ->latest()
            ->paginate(20);

        return view('provider.payments.history', compact('payments'));
    }

    /**
     * Payment detail page.
     */
    public function showPayment($id)
    {
        $provider = Auth::user()->ownProvider();

        $payment = Payment::with(['payable', 'provider'])
            ->where('provider_id', $provider?->id)
            ->findOrFail($id);

        return view('provider.payments.detail', compact('payment'));
    }

    /**
     * Show payment page for a subscription.
     */
    public function show($subscriptionId)
    {
        $subscription = Subscription::with(['plan', 'provider'])
            ->where('id', $subscriptionId)
            ->where('provider_id', Auth::user()->ownProvider()?->id)
            ->firstOrFail();

        return view('provider.payments.show', compact('subscription'));
    }

    /**
     * Create payment intent for subscription.
     */
    public function createPayment(Request $request, $subscriptionId)
    {
        $subscription = Subscription::with(['plan', 'provider'])
            ->where('id', $subscriptionId)
            ->where('provider_id', Auth::user()->ownProvider()?->id)
            ->firstOrFail();

        $result = $this->paymentService->createSubscriptionPayment($subscription);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret'],
                'payment_id' => $result['payment']->payment_id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Payment failed',
        ], 400);
    }

    /**
     * Confirm payment after Stripe callback.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        $result = $this->paymentService->confirmPayment($request->payment_id);

        if ($result) {
            return redirect()->route('provider.subscriptions.index')
                ->with('success', 'Payment successful! Your subscription is now active.');
        }

        return redirect()->route('provider.subscriptions.index')
            ->with('error', 'Payment failed. Please try again.');
    }
}