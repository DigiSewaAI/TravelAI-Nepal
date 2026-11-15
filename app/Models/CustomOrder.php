<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomOrder extends Model
{
    protected $fillable = [
        'booking_number', 'provider_id', 'client_name', 'client_email',
        'type', 'budget', 'deadline', 'status',
        'request_type', 'ready_made_artwork_id',
        'description', 'materials', 'dimensions', 'quantity',
        'reference_image_path', 'pickup_address', 'delivery_address',
        'quoted_amount', 'quoted_at', 'accepted_quote_id', 'accepted_quote_currency',
        'production_started_at', 'ready_at', 'completed_at', 'cancelled_at',
        'cancellation_requested', 'cancellation_reason',
        'artwork_name_snapshot', 'artwork_price_snapshot', 'artwork_currency_snapshot',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'date',
        'quoted_amount' => 'decimal:2',
        'quoted_at' => 'datetime',
        'production_started_at' => 'datetime',
        'ready_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancellation_requested' => 'boolean',
        'artwork_price_snapshot' => 'decimal:2',
        'quantity' => 'integer',
    ];

    private const CUSTOMER_TRANSITIONS = [
        'quoted' => ['accepted', 'revision_requested', 'rejected'],
        'ready_for_delivery' => ['completed'],
    ];

    private const PROVIDER_TRANSITIONS = [
        'pending' => ['under_review', 'cancelled'],
        'under_review' => ['quoted', 'cancelled'],
        'revision_requested' => ['under_review', 'quoted'],
        'accepted' => ['in_progress', 'cancelled'],
        'in_progress' => ['ready_for_delivery'],
        'ready_for_delivery' => ['completed'],
    ];

    private const TERMINAL = ['rejected', 'cancelled', 'completed'];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function readyMadeArtwork() { return $this->belongsTo(Artwork::class, 'ready_made_artwork_id'); }
    public function quotes() { return $this->hasMany(CustomOrderQuote::class, 'custom_order_id')->orderBy('version'); }
    public function acceptedQuote() { return $this->belongsTo(CustomOrderQuote::class, 'accepted_quote_id'); }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL, true);
    }

    public function canTransitionTo(string $newStatus, string $role): bool
    {
        if ($this->isTerminal() && $this->status !== 'completed') {
            return false; // rejected/cancelled = terminal
        }

        $allowed = match ($role) {
            'customer' => self::CUSTOMER_TRANSITIONS[$this->status] ?? [],
            'provider' => self::PROVIDER_TRANSITIONS[$this->status] ?? [],
            'admin' => [], // admin bypass (explicit only)
            default => [],
        };

        return in_array($newStatus, $allowed, true);
    }

    public function nextQuoteVersion(): int
    {
        return ($this->quotes()->max('version') ?? 0) + 1;
    }

    /**
     * Concurrency-safe booking number generation.
     * MUST be called inside a DB transaction for lockForUpdate to work.
     */
    public static function generateBookingNumber(): string
    {
        $year = date('Y');
        $prefix = 'ART-' . $year . '-';
        $maxAttempts = 10;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $maxSeq = (int) (DB::table('custom_orders')
                ->where('booking_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->selectRaw('MAX(CAST(SUBSTRING(booking_number, -4) AS UNSIGNED)) as max_seq')
                ->value('max_seq') ?? 0);

            $candidate = $prefix . str_pad($maxSeq + 1 + $attempt, 4, '0', STR_PAD_LEFT);

            if (!DB::table('custom_orders')->where('booking_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Booking number generation failed after ' . $maxAttempts . ' attempts');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->booking_number)) {
                $model->booking_number = static::generateBookingNumber();
            }
        });
    }
}