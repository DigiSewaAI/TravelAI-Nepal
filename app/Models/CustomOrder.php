<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomOrder extends Model
{
    protected $fillable = [
        'provider_id', 'client_name', 'client_email',
        'type', 'budget', 'deadline', 'status'
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'deadline' => 'date',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
}