<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiReservation extends Model
{
    use HasFactory;

    protected $table = 'ai_reservations';

    protected $fillable = [
    'provider_id',
    'guest_ip_hash',
    'period',
    'status',
    'reserved_at',
    'finalized_at',
    'released_at',
    'expires_at',
    'idempotency_key',
    'endpoint',
];

    protected $casts = [
        'reserved_at' => 'datetime',
        'finalized_at' => 'datetime',
        'released_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }
}