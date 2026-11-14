<?php

namespace App\Mail;

use App\Models\Provider;
use App\Models\RestaurantOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RestaurantOrderPlacedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public RestaurantOrder $order,
        public Provider $provider,
        public ?string $plainCode = null,
        public array $brand = []
    ) {}

    /**
     * Legacy build() API — required for inline image embedding (CID).
     * The new content() API does NOT expose $message in views.
     */
    public function build()
    {
        $this->order->loadMissing(['items', 'table']);
        
        $brandName = $this->brand['name'] ?? $this->provider->name ?? 'TravelAI Nepal';

        return $this->subject('Order Confirmed — ' . $this->order->order_number . ' · ' . $brandName)
                    ->view('emails.restaurant-order-placed')
                    ->with([
                        'order'     => $this->order,
                        'provider'  => $this->provider,
                        'brand'     => $this->brand,
                        'plainCode' => $this->plainCode,
                    ]);
    }

    public function attachments(): array
    {
        $attachments = [];

        // PDF receipt (regular attachment only)
        $filename = 'receipt-' . $this->order->order_number . '.pdf';
        $path = 'receipts/' . $filename;

        if (Storage::disk('local')->exists($path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $path)
                ->as($filename)
                ->withMime('application/pdf');
        }

        // Logo is embedded directly in the view via $message->embedData()
        return $attachments;
    }
}