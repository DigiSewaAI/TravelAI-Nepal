<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Provider;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $email = $provider->user->email ?? $provider->email;

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
}