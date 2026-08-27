<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $table = 'waitlist'; // ✅ यो Line थप्नुहोस्
    protected $fillable = ['email'];
}