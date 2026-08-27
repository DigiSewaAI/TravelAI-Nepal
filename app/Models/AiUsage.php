<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiUsage extends Model
{
    use HasFactory;

    protected $table = 'ai_usage'; // ✅ यो थप्नुहोस्

    protected $fillable = ['provider_id', 'count', 'month'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}