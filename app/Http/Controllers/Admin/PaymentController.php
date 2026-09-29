<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentVerifiedMail;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Display a listing of all payments.
     */
    public function index()
    {
        $payments = Payment::with(['provider', 'user', 'payable'])
            ->latest()
            ->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Display the specified payment details.
     * Loads relationships for provider, user, and payable (polymorphic).
     */
    public function show(Payment $payment)
    {
        // Eager load relationships for the view
        $payment->load(['provider', 'user', 'payable']);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Refund a payment (mark as refunded).
     */
    public function refund(Payment $payment)
    {
        // Only allow refund if status is 'success'
        if ($payment->status !== 'success') {
            return back()->with('error', 'Only successful payments can be refunded.');
        }

        $payment->status = 'refunded';
        $payment->save();

        return back()->with('success', 'Payment refunded successfully.');
    }

    /**
     * Delete a payment record.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
        /**
     * PHASE 7D — Admin verify queue (pending payments list).
     */
    public function verifyQueue()
    {
        $pending = Payment::with(['provider', 'payable'])
            ->where('status', 'pending_verification')
            ->latest()
            ->paginate(20);

        $stats = [
            'pending'  => Payment::where('status', 'pending_verification')->count(),
            'verified' => Payment::where('status', 'verified')
                ->whereDate('verified_at', today())->count(),
            'rejected' => Payment::where('status', 'rejected')
                ->where('updated_at', '>=', now()->subWeek())->count(),
            'revenue'  => Payment::where('status', 'verified')
                ->whereMonth('verified_at', now()->month)
                ->sum('amount'),
        ];

        $recentActivity = Payment::with('provider')
            ->whereIn('status', ['verified', 'rejected'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('admin.payments.verify', compact('pending', 'stats', 'recentActivity'));
    }

    /**
     * PHASE 7D — Approve payment (atomic: Payment + Subscription).
     */
    public function approve(Payment $payment)
    {
        if ($payment->status !== 'pending_verification') {
            return back()->with('error', 'This payment is not pending verification.');
        }

        DB::transaction(function () use ($payment) {
            $locked = Payment::where('id', $payment->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending_verification') {
                throw new \RuntimeException('Payment status changed — refresh and retry.');
            }

            $locked->update([
                'status'      => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'paid_at'     => $locked->paid_at ?? now(),
            ]);

            if ($locked->payable_type === Subscription::class) {
                $subscription = Subscription::find($locked->payable_id);
                if ($subscription) {
                    $subscription->update([
                        'status'     => 'active',
                        'start_date' => $subscription->start_date ?? now(),
                    ]);

                    // Cancel other active subscriptions for same provider
                    Subscription::where('provider_id', $subscription->provider_id)
                        ->where('id', '!=', $subscription->id)
                        ->where('status', 'active')
                        ->update(['status' => 'cancelled', 'end_date' => now()]);
                }
            }
        });

        Log::info('Payment approved', [
            'payment_id'  => $payment->id,
            'verified_by' => Auth::id(),
        ]);

        // PHASE 7G: Generate invoice + send email (non-blocking)
        $payment->refresh();
        if ($payment->payable_type === Subscription::class) {
            $subscription = Subscription::with(['provider', 'plan'])->find($payment->payable_id);

            if ($subscription && $subscription->provider && $subscription->provider->contact_email) {
                $invoice = null;
                $pdf = null;

                // Generate invoice
                try {
                    $invoice = app(InvoiceService::class)->generateForSubscription($subscription);
                } catch (\Throwable $e) {
                    Log::error('Invoice generation failed', [
                        'subscription_id' => $subscription->id,
                        'error'           => $e->getMessage(),
                    ]);
                }

                // Generate PDF (if invoice created)
                if ($invoice) {
                    try {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('provider.invoices.pdf', ['invoice' => $invoice]);
                    } catch (\Throwable $e) {
                        Log::error('Invoice PDF generation failed', [
                            'invoice_id' => $invoice->id,
                            'error'      => $e->getMessage(),
                        ]);
                    }
                }

                // Send email (fail-safe)
                try {
                    Mail::to($subscription->provider->contact_email)
                        ->send(new PaymentVerifiedMail($payment, $subscription, $invoice, $pdf));
                } catch (\Throwable $e) {
                    Log::error('Payment verified email failed', [
                        'payment_id'      => $payment->id,
                        'subscription_id' => $subscription->id,
                        'error'           => $e->getMessage(),
                    ]);
                }
            }
        }

        return back()->with('success', 'Payment approved. Subscription activated.');
    }

    /**
     * PHASE 7D — Reject payment (reason required).
     */
    public function reject(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'admin_note' => 'required|string|min:10|max:500',
        ]);

        if ($payment->status !== 'pending_verification') {
            return back()->with('error', 'This payment is not pending verification.');
        }

        DB::transaction(function () use ($payment, $validated) {
            $locked = Payment::where('id', $payment->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending_verification') {
                throw new \RuntimeException('Payment status changed — refresh and retry.');
            }

            $locked->update([
                'status'      => 'rejected',
                'admin_note'  => trim($validated['admin_note']),
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'metadata'    => array_merge($locked->metadata ?? [], [
                    'refund_reminder'    => true,
                    'refund_reminded_at' => now()->toIso8601String(),
                ]),
            ]);

            if ($locked->payable_type === Subscription::class) {
                $subscription = Subscription::find($locked->payable_id);
                if ($subscription) {
                    $subscription->update([
                        'status'   => 'cancelled',
                        'end_date' => now(),
                    ]);
                }
            }
        });

        Log::info('Payment rejected', [
            'payment_id'  => $payment->id,
            'verified_by' => Auth::id(),
            'reason'      => $validated['admin_note'],
        ]);

        return back()->with('success', 'Payment rejected. Provider notified via dashboard.');
    }

    /**
     * PHASE E1-REFUND — Mark a rejected payment's refund as completed.
     * Manual refund (bank) — this is bookkeeping only.
     */
    public function markRefunded(Payment $payment)
    {
        if ($payment->status !== 'rejected') {
            return back()->with('error', 'Only rejected payments can be marked as refunded.');
        }

        $metadata = $payment->metadata ?? [];
        if (!empty($metadata['refund_marked_at'])) {
            return back()->with('info', 'Refund already marked as completed.');
        }

        $metadata['refund_marked_at'] = now()->toIso8601String();
        $payment->update(['metadata' => $metadata]);

        Log::info('Payment refund marked', [
            'payment_id' => $payment->id,
            'marked_by'  => Auth::id(),
        ]);

        return back()->with('success', 'Refund marked as completed.');
    }

    /**
     * PHASE 7D — Serve receipt file (admin-only, private storage).
     */
    public function serveReceipt(Payment $payment)
    {
        if (!$payment->receipt_image_path) {
            abort(404, 'No receipt for this payment.');
        }

        if (!Storage::disk('local')->exists($payment->receipt_image_path)) {
            abort(404, 'Receipt file not found.');
        }

        $path = Storage::disk('local')->path($payment->receipt_image_path);
        return response()->file($path);
    }
}