<?php

namespace App\Mail;

use App\Models\CustomOrder;
use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderArtCraftReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $provider;

    public function __construct(CustomOrder $order, Provider $provider)
    {
        $this->order = $order;
        $this->provider = $provider;
    }

    public function build()
    {
        return $this->subject('New ArtCraft Enquiry 🎨 — TravelAI Nepal')
                    ->view('emails.provider-artcraft-received');
    }
}