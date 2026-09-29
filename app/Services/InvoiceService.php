<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Provider;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Illuminate\Support\Str;

class InvoiceService
{
    /**
     * Generate a unique invoice number
     */
    public function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique receipt number
     */
    public function generateReceiptNumber(): string
    {
        return 'REC-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create an invoice from a successful payment
     */
    public function createFromPayment(Payment $payment, $payable): Invoice
    {
        $invoice = Invoice::create([
            'provider_id' => $payment->provider_id,
            'subscription_id' => $payable instanceof Subscription ? $payable->id : null,
            'booking_id' => null, // Adjust if you have booking logic
            'invoice_number' => $this->generateInvoiceNumber(),
            'receipt_number' => $this->generateReceiptNumber(),
            'amount' => $payment->amount,
            'currency' => $payment->currency ?? 'USD',
            'tax' => 0, // Can be calculated later
            'total' => $payment->amount,
            'status' => 'paid',
            'payment_method' => $payment->gateway,
            'paid_at' => $payment->paid_at ?? now(),
            'due_date' => now()->addDays(30),
            'metadata' => [
                'payment_id' => $payment->id,
                'plan_name' => $payable->plan->name ?? null,
                'billing_interval' => $payable->billing_interval ?? null,
            ],
        ]);

        return $invoice;
    }

    /**
     * Generate PDF for an invoice
     */
    public function generatePdf(Invoice $invoice)
    {
        $data = [
            'invoice' => $invoice,
            'provider' => $invoice->provider,
            'subscription' => $invoice->subscription,
            'booking' => $invoice->booking,
        ];

        return Pdf::loadView('invoices.pdf', $data);
    }

    /**
     * Send invoice email to the provider
     */
    public function sendInvoiceEmail(Invoice $invoice): void
    {
        $provider = $invoice->provider;
        $email = $provider->contact_email ?? ($provider->user->email ?? null);

        if ($email) {
            $pdf = $this->generatePdf($invoice);
            Mail::to($email)->send(new InvoiceMail($invoice, $pdf));
        }
    }

    /**
     * Create invoice from payment and send email to provider
     */
    public function createAndSend(Payment $payment, $payable): Invoice
    {
        $invoice = $this->createFromPayment($payment, $payable);
        $this->sendInvoiceEmail($invoice);
        return $invoice;
    }

    /**
     * PHASE 7G — Generate race-safe invoice for a verified subscription.
     * Used by Admin\PaymentController@approve after payment verification.
     *
     * Transaction + lock + retry prevents duplicate invoice numbers
     * under concurrent verification.
     */
    public function generateForSubscription(Subscription $subscription): ?Invoice
    {
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () use ($subscription) {
                    $year = now()->year;

                    $lastInvoice = Invoice::whereYear('created_at', $year)
                        ->lockForUpdate()
                        ->orderBy('invoice_number', 'desc')
                        ->first();

                    $next = 1;
                    if ($lastInvoice && preg_match('/INV-\d{4}-(\d+)$/', $lastInvoice->invoice_number, $m)) {
                        $next = ((int) $m[1]) + 1;
                    }

                    $invoiceNumber = sprintf('INV-%d-%06d', $year, $next);
                    $receiptNumber = sprintf('REC-%d-%06d', $year, $next);

                    $amount = (float) ($subscription->plan->price_monthly ?? 0);
                    $tax = round($amount * 0.13, 2);       // Nepal VAT 13%
                    $total = round($amount + $tax, 2);

                    return Invoice::create([
                        'provider_id'     => $subscription->provider_id,
                        'subscription_id' => $subscription->id,
                        'booking_id'      => null,
                        'invoice_number'  => $invoiceNumber,
                        'receipt_number'  => $receiptNumber,
                        'amount'          => $amount,
                        'currency'        => 'NPR',
                        'tax'             => $tax,
                        'total'           => $total,
                        'status'          => 'paid',
                        'payment_method'  => 'manual_verify',
                        'paid_at'         => now(),
                        'due_date'        => null,
                        'metadata'        => [
                            'source'    => 'payment_verified',
                            'plan_slug' => $subscription->plan->slug ?? null,
                            'plan_name' => $subscription->plan->name ?? null,
                        ],
                    ]);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                if ($attempt >= $maxAttempts || !str_contains($e->getMessage(), 'invoice_number')) {
                    Log::error('InvoiceService: generateForSubscription failed', [
                        'subscription_id' => $subscription->id,
                        'attempt'         => $attempt,
                        'error'           => $e->getMessage(),
                    ]);
                    return null;
                }
                usleep(50000);
            }
        }

        return null;
    }
}