<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtCraftDetail extends Model
{
    protected $fillable = [
        'service_id', 'craft_type', 'materials', 'artist_name', 'dimensions', 'is_handmade'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}