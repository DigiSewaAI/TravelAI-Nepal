<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_provider_type');
    }
}