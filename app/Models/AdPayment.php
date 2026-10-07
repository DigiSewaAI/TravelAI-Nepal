<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdPayment extends Model
{
    protected $fillable = [
        'ad_id', 'provider_id', 'amount', 'currency',
        'payment_method', 'payment_reference', 'proof_path',
        'status', 'admin_notes', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'amount'      => 'integer',
        'verified_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}