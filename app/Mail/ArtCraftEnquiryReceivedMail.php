<?php

namespace App\Mail;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ArtCraftEnquiryReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public CustomOrder $order) {}

    public function envelope(): Envelope
    {
        $providerName = $this->order->provider->name ?? 'Artisan';
        $type = $this->order->request_type === 'ready_made' ? 'Enquiry' : 'Custom Request';
        return new Envelope(
            subject: $type . ' Received — ' . $providerName,
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing(['provider', 'readyMadeArtwork']);

        $logoData = null;
        $logoUrl = null;
        $provider = $this->order->provider;

        if ($provider && $provider->hasFeature('custom_logo') && $provider->logo_url) {
            $logoPath = storage_path('app/public/' . $provider->logo_url);
            if (file_exists($logoPath)) {
                if (filesize($logoPath) <= 51200) {
                    $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                    $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                } else {
                    $logoUrl = url('storage/' . $provider->logo_url);
                }
            }
        }

        return new Content(
            view: 'emails.artcraft-enquiry-received',
            with: [
                'order' => $this->order,
                'provider' => $provider,
                'isCustom' => $this->order->request_type === 'custom',
                'logoData' => $logoData,
                'logoUrl'  => $logoUrl,
            ]
        );
    }
}