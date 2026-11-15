<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artwork extends Model
{
    protected $fillable = [
        'provider_id', 'name', 'artist_name', 'category',
        'price', 'status', 'emoji', 'gradient',
        'image_path', 'additional_images', 'description', 'materials', 'dimensions',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'additional_images' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
}