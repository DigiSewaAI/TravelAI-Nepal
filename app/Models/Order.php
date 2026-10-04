<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status', 'contact_name', 'contact_email',
        'contact_phone', 'shipping_address', 'shipping_city', 'shipping_country',
        'subtotal', 'shipping_fee', 'total', 'currency',
        'payment_method', 'payment_status', 'paid_at', 'notes',
    ];

    protected $casts = [
        'paid_at'      => 'datetime',
        'total'        => 'decimal:2',
        'subtotal'     => 'decimal:2',
        'shipping_fee' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $year = now()->format('y');
                $last = static::where('order_number', 'LIKE', "ORD-{$year}-%")
                    ->orderByDesc('order_number')
                    ->value('order_number');
                $next = $last ? ((int) substr($last, -5)) + 1 : 1;
                $order->order_number = sprintf('ORD-%s-%05d', $year, $next);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeForUser($q, $uid)
    {
        return $q->where('user_id', $uid);
    }

    public function scopeWithStatus($q, $status)
    {
        return $q->where('status', $status);
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isPaid(): bool       { return $this->payment_status === 'paid'; }
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }
}