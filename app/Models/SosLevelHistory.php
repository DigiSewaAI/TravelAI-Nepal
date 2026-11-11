<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SosLevelHistory extends Model {
    protected $table = 'sos_level_history';
    public $timestamps = false;
    protected $fillable = ['sos_alert_id','from_level','to_level','changed_by','reason','created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function sosAlert()  { return $this->belongsTo(SosAlert::class, 'sos_alert_id'); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}