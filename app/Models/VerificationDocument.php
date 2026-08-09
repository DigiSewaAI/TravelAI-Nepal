<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationDocument extends Model
{
    use HasFactory;

    protected $fillable = ['provider_id', 'type', 'file_path', 'status'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}