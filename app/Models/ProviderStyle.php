<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderStyle extends Model
{
    protected $table = 'provider_styles';

    protected $fillable = [
        'provider_id',
        'style_slug',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}