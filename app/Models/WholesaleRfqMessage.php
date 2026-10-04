<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleRfqMessage extends Model
{
    protected $fillable = ['rfq_id', 'sender_id', 'sender_role', 'message'];

    public function rfq()
    {
        return $this->belongsTo(WholesaleRfq::class, 'rfq_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}