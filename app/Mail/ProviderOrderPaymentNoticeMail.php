<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderOrderPaymentNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $paymentReference;

    public function __construct(Order $order, string $paymentReference)
    {
        $this->order = $order;
        $this->paymentReference = $paymentReference;
    }

    public function build()
    {
        return $this->subject('Payment Received 💰 — Order ' . $this->order->order_number)
                    ->view('emails.provider-order-payment-notice');
    }
}