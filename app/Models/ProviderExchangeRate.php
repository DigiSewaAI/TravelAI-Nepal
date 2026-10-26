<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderExchangeRate extends Model
{
    protected $fillable = [
        'provider_id', 'service_id', 'currency_code', 'currency_name', 'flag_emoji',
        'buy_rate', 'sell_rate', 'is_active', 'effective_date',
    ];

    protected $casts = [
        'buy_rate'       => 'decimal:4',
        'sell_rate'      => 'decimal:4',
        'is_active'      => 'boolean',
        'effective_date' => 'date',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function service()  { return $this->belongsTo(Service::class); }
}