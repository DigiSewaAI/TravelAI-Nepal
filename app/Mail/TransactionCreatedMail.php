<?php

namespace App\Mail;

use App\Models\ExchangeTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public ExchangeTransaction $transaction;

    public function __construct(ExchangeTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function build()
    {
        return $this->subject('Your TravelAI Nepal Transaction ID: ' . $this->transaction->transaction_number)
            ->view('emails.transaction-created')
            ->with([
                'transaction' => $this->transaction,
                'claimUrl' => route('traveler.moneychanger.claim.form'),
                'loginUrl' => url('/login'),
                'registerUrl' => url('/register'),
                'isRegistered' => \App\Models\User::where('email', $this->transaction->customer_email)->exists(),
            ]);
    }
}