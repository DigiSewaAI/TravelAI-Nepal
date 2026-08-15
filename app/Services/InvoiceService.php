<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    public function generateReceiptNumber(): string
    {
        return 'REC-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    public function createFromPayment(Payment $payment, $payable): Invoice
    {
        $invoice = Invoice::create([
            'provider_id' => $payment->provider_id,
            'subscription_id' => $payable instanceof Subscription ? $payable->id : null,
            'booking_id' => null,
            'invoice_number' => $this->generateInvoiceNumber(),
            'receipt_number' => $this->generateReceiptNumber(),
            'amount' => $payment->amount,
            'currency' => $payment->currency ?? 'USD',
            'tax' => 0,
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
}