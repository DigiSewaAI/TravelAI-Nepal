<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artwork extends Model
{
    protected $fillable = [
        'provider_id', 'name', 'artist_name', 'category',
        'price', 'status', 'emoji', 'gradient'
    ];

    protected $casts = ['price' => 'decimal:2'];

    public function provider() { return $this->belongsTo(Provider::class); }
}