<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\PlatformPaymentMethod;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class PaymentController extends Controller
{
    /**
     * Resolve vertical-specific layout based on provider type.
     */
    private function getProviderLayout($provider): string
    {
        $map = [
            'restaurant'         => 'layouts.provider-restaurant',
            'hotel'              => 'layouts.provider-hotel',
            'resort'             => 'layouts.provider-hotel',
            'lodge'              => 'layouts.provider-hotel',
            'homestay'           => 'layouts.provider-hotel',
            'transport-provider' => 'layouts.provider-transport',
            'shop-owner'         => 'layouts.provider-inventory',
            'rental-provider'    => 'layouts.provider-inventory',
            'wholesale-provider' => 'layouts.provider-inventory',
            'art-craft'          => 'layouts.provider-art-craft',
            'guide'              => 'layouts.provider-individual',
            'porter'             => 'layouts.provider-individual',
            'photographer'       => 'layouts.provider-individual',
            'money-changer'      => 'layouts.provider-money-changer',
        ];
        return $map[$provider?->types->first()?->slug] ?? 'layouts.provider';
    }
    protected $paymentService;
    protected $invoiceService;             // <-- Added

    public function __construct(PaymentService $paymentService, InvoiceService $invoiceService)
    {
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;   // <-- Injected
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

        $provider_layout = $this->getProviderLayout($provider);

        $stats = [
            'total' => $payments->count(),
            'paid' => $payments->whereIn('status', ['completed','paid','success'])->count(),
            'pending' => $payments->where('status', 'pending')->count(),
        ];

        return view(($provider_layout === 'layouts.provider') ? 'provider.payments.history' : 'provider.payments.history-premium', compact('payments', 'provider_layout', 'stats'));
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

        $provider_layout = $this->getProviderLayout($provider);
return view(($provider_layout === 'layouts.provider') ? 'provider.payments.detail' : 'provider.payments.detail-premium', compact('payment', 'provider_layout'));
    }

        /**
     * PHASE 7C — Provider subscription payment page.
     * Displays platform payment methods (bank/eSewa/Khalti) + submit proof form.
     */
    public function show($subscriptionId)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $subscription = Subscription::with(['plan', 'provider'])
            ->where('provider_id', $provider->id)
            ->findOrFail($subscriptionId);

        // Block access if already active/paid
        if ($subscription->status === 'active') {
            return redirect()
                ->route('provider.subscriptions.index')
                ->with('success', 'Subscription is already active.');
        }

        // Load active platform payment methods (admin-configured)
        $platformMethods = PlatformPaymentMethod::active()->get();

        // Check for existing pending payment (duplicate submit prevention)
        $pendingPayment = Payment::where('payable_type', Subscription::class)
            ->where('payable_id', $subscription->id)
            ->whereIn('status', ['pending', 'pending_verification'])
            ->latest()
            ->first();

        $provider_layout = $this->getProviderLayout($provider);
return view(($provider_layout === 'layouts.provider') ? 'provider.payments.show' : 'provider.payments.show-premium', compact('subscription',
            'platformMethods',
            'pendingPayment', 'provider_layout'));
    }

        /**
     * PHASE 7C — Provider submits payment proof for manual verification.
     */
    public function createPayment(Request $request, $subscriptionId)
    {
        $provider = Auth::user()->getCurrentProvider();
        abort_unless($provider, 403);

        $subscription = Subscription::with('plan')
            ->where('provider_id', $provider->id)
            ->findOrFail($subscriptionId);

        if ($subscription->status === 'active') {
            return back()->with('error', 'Subscription is already active.');
        }

        $validated = $request->validate([
            'reference_number' => 'required|string|max:255',
            'receipt'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Duplicate prevention — one pending payment per subscription
        $existing = Payment::where('payable_type', Subscription::class)
            ->where('payable_id', $subscription->id)
            ->whereIn('status', ['pending', 'pending_verification'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'A payment is already pending verification.');
        }

        // Store receipt privately
        $path = $request->file('receipt')->store('receipts', 'local');

        // Create payment record
        $payment = Payment::create([
            'payable_type'        => Subscription::class,
            'payable_id'          => $subscription->id,
            'provider_id'         => $provider->id,
            'payment_id'          => 'manual-' . now()->timestamp . '-' . Str::random(8),
            'gateway'             => 'manual',
            'amount'              => $subscription->plan->price_monthly ?? 0,
            'currency'            => 'NPR',
            'status'              => 'pending_verification',
            'reference_number'    => trim($validated['reference_number']),
            'receipt_image_path'  => $path,
            'metadata'            => [
                'plan_id'          => $subscription->plan_id,
                'billing_interval' => $subscription->billing_interval ?? 'monthly',
                'submitted_at'     => now()->toIso8601String(),
            ],
        ]);

        Log::info('Provider payment submitted for verification', [
            'payment_id'      => $payment->id,
            'provider_id'     => $provider->id,
            'subscription_id' => $subscription->id,
        ]);

                // NOTIFICATION-PRIORITY-01: Notify Admin about Payment Proof
        try {
            \Illuminate\Support\Facades\Notification::route('mail', env('ADMIN_EMAIL', 'admin@travelainepal.com'))
                ->notify(new \App\Notifications\AdminAlertNotification(
                    'New Payment Proof Submitted',
                    'Provider **' . $provider->name . '** has submitted payment proof for the **' . ($subscription->plan->name ?? 'Plan') . '** plan. Amount: NPR ' . number_format($payment->amount, 2) . '. Reference: ' . $payment->reference_number,
                    route('admin.payments.verify') // ✅ Verified from route:list
                ));
        } catch (\Exception $e) {
            Log::error('Failed to send Admin Payment Notification: ' . $e->getMessage());
        }

        return redirect()
            ->route('provider.payments.show', $subscription->id)
            ->with('success', 'Payment proof submitted. Verification within 24 hours.');
    }

    /**
     * Confirm payment (legacy — Phase 7C will replace).
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
        ]);

        $result = $this->paymentService->confirmPayment($request->payment_id);

        if ($result) {
            // 🔥 After successful payment, create and send invoice
            $payment = Payment::where('payment_id', $request->payment_id)->first();

            if ($payment && $payment->payable_type === 'App\Models\Subscription') {
                $subscription = $payment->payable;
                $this->invoiceService->createAndSend($payment, $subscription);
            }

            return redirect()->route('provider.subscriptions.index')
                ->with('success', 'Payment successful! Your subscription is now active.');
        }

        return redirect()->route('provider.subscriptions.index')
            ->with('error', 'Payment failed. Please try again.');
    }
}