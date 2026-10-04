<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'order_status_histories';

    protected $fillable = [
        'order_id', 'order_item_id', 'from_status', 'to_status',
        'changed_by', 'changed_by_role', 'note', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public static function record(
        Order $order,
        string $toStatus,
        ?string $fromStatus = null,
        ?OrderItem $item = null,
        ?int $changedBy = null,
        string $role = 'system',
        ?string $note = null
    ): self {
        return static::create([
            'order_id'        => $order->id,
            'order_item_id'   => $item?->id,
            'from_status'     => $fromStatus,
            'to_status'       => $toStatus,
            'changed_by'      => $changedBy,
            'changed_by_role' => $role,
            'note'            => $note,
            'created_at'      => now(),
        ]);
    }
}