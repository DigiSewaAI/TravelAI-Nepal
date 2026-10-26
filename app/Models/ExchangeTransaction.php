<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeTransaction extends Model
{
    protected $fillable = [
        'transaction_number', 'provider_id', 'service_id',
        'customer_name', 'customer_initials', 'type', 'currency_code',
        'amount_foreign', 'rate', 'npr_amount', 'profit', 'status', 'notes',
    ];

    protected $casts = [
        'amount_foreign' => 'decimal:2',
        'rate'           => 'decimal:4',
        'npr_amount'     => 'decimal:2',
        'profit'         => 'decimal:2',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function service()  { return $this->belongsTo(Service::class); }
}