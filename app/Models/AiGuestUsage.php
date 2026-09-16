<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiGuestUsage extends Model
{
    use HasFactory;

    protected $table = 'ai_guest_usage';

    protected $fillable = ['guest_ip_hash', 'count', 'period'];

    protected $casts = [
        'count' => 'integer',
    ];
}