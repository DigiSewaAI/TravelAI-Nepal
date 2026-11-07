<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualBooking extends Model
{
    protected $fillable = [
        'provider_id', 'service_type', 'client_name', 'customer_email', 'receipt_code_hash', 'receipt_code_issued_at', 'receipt_code_used_at', 'title', 'pax',
        'start_date', 'duration', 'amount', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }

    /**
     * Auto-generate booking_number on create.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->booking_number)) {
                $year = date('Y');
                $lastId = static::whereYear('created_at', $year)->max('id') ?? 0;
                $nextNum = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
                $model->booking_number = 'IND-' . $year . '-' . $nextNum;
            }
        });
    }
}