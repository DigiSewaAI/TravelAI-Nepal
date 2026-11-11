<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SosAlert extends Model
{
    use HasFactory;

    // SOS v1.2 - Emergency level severity (higher = more severe)
    private const LEVEL_SEVERITY = [
        'assistance'   => 1,
        'urgent'       => 2,
        'unclassified' => 2, // = urgent - assessment state, admin classify
        'critical'     => 3,
    ];

    protected $table = 'sos_alerts';

    protected $fillable = [
        'traveler_id', 'provider_id', 'status',
        'sent_at', 'acknowledged_at', 'acknowledged_by',
        'responding_at', 'responding_by',
        'resolved_at', 'resolved_by',
        'false_alarm_reason', 'auto_escalated_at', 'admin_notified_at',
        'latitude', 'longitude', 'message',
        'trekker_id', 'booking_id', 'is_resolved',
        // SOS v1.2 - Emergency Level Layer
        'emergency_level', 'level_changed_at', 'level_changed_by', 'cancel_reason',
    ];

    protected $casts = [
        'latitude'          => 'decimal:7',
        'longitude'         => 'decimal:7',
        'is_resolved'       => 'boolean',
        'sent_at'           => 'datetime',
        'acknowledged_at'   => 'datetime',
        'responding_at'     => 'datetime',
        'resolved_at'       => 'datetime',
        'auto_escalated_at' => 'datetime',
        'admin_notified_at' => 'datetime',
        'level_changed_at'  => 'datetime',   // SOS v1.2 NEW
    ];

    // Relations
    public function traveler()       { return $this->belongsTo(User::class, 'traveler_id'); }
    public function provider()       { return $this->belongsTo(Provider::class, 'provider_id'); }
    public function booking()        { return $this->belongsTo(Booking::class, 'booking_id'); }
    public function acknowledgedBy() { return $this->belongsTo(User::class, 'acknowledged_by'); }
    public function respondingBy()   { return $this->belongsTo(User::class, 'responding_by'); }
    public function resolvedBy()     { return $this->belongsTo(User::class, 'resolved_by'); }
    public function actions()        { return $this->hasMany(SosAlertAction::class, 'sos_alert_id')->latest(); }

    // SOS v1.2 - Level relations
    public function levelHistory()   { return $this->hasMany(SosLevelHistory::class, 'sos_alert_id')->latest(); }
    public function levelChangedBy() { return $this->belongsTo(User::class, 'level_changed_by'); }

    // Scopes
    public function scopePending($query)      { return $query->where('status', 'pending'); }
    public function scopeActive($query)       { return $query->whereIn('status', ['pending', 'sent']); }
    public function scopeInProgress($query)   { return $query->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding']); }
    public function scopeTerminal($query)     { return $query->whereIn('status', ['resolved', 'false_alarm']); }

    // State predicates
    public function isPending(): bool      { return in_array($this->status, ['pending', 'sent']); }
    public function isAcknowledged(): bool { return $this->status === 'acknowledged'; }
    public function isResponding(): bool   { return $this->status === 'responding'; }
    public function isResolved(): bool     { return $this->status === 'resolved'; }
    public function isFalseAlarm(): bool   { return $this->status === 'false_alarm'; }
    public function isTerminal(): bool     { return in_array($this->status, ['resolved', 'false_alarm']); }
    public function isInProgress(): bool   { return !$this->isTerminal(); }

    // Permission checks
    public function canAcknowledge(): bool    { return $this->isPending(); }
    public function canRespond(): bool        { return $this->isPending() || $this->isAcknowledged(); }
    public function canResolve(): bool        { return !$this->isTerminal(); }
    public function canMarkFalseAlarm(): bool { return $this->isPending() || $this->isAcknowledged(); }

    // State transitions (return bool - true if changed)
    public function acknowledge(int $userId): bool
    {
        if (!$this->canAcknowledge()) return false;
        $from = $this->status;
        $this->update([
            'status'          => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
        $this->logAction('acknowledged', $userId, $from, 'acknowledged');
        return true;
    }

    public function respond(int $userId): bool
    {
        if (!$this->canRespond()) return false;
        $from = $this->status;
        $this->update([
            'status'        => 'responding',
            'responding_at' => now(),
            'responding_by' => $userId,
        ]);
        $this->logAction('responding', $userId, $from, 'responding');
        return true;
    }

    public function markResolved(int $userId, ?string $notes = null): bool
    {
        if (!$this->canResolve()) return false;
        $from = $this->status;
        $this->update([
            'status'      => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId,
            'is_resolved' => true,
        ]);
        $this->logAction('resolved', $userId, $from, 'resolved', $notes);
        return true;
    }

    public function markFalseAlarm(int $userId, string $reason): bool
    {
        if (!$this->canMarkFalseAlarm()) return false;
        $from = $this->status;
        $this->update([
            'status'             => 'false_alarm',
            'resolved_at'        => now(),
            'resolved_by'        => $userId,
            'false_alarm_reason' => $reason,
            'is_resolved'        => true,
        ]);
        $this->logAction('false_alarm', $userId, $from, 'false_alarm', $reason);
        return true;
    }

    public function logAction(string $action, ?int $userId, ?string $from, ?string $to, ?string $notes = null, array $metadata = []): void
    {
        SosAlertAction::create([
            'sos_alert_id' => $this->id,
            'user_id'      => $userId,
            'action'       => $action,
            'from_status'  => $from,
            'to_status'    => $to,
            'notes'        => $notes,
            'metadata'     => !empty($metadata) ? $metadata : null,
        ]);
    }

    // ========================================================
    // SOS v1.2 - Emergency Level
    // ========================================================

    public function hasLevel(): bool
    {
        return !is_null($this->emergency_level);
    }

    public function canChangeLevel(): bool
    {
        return !$this->isTerminal();
    }

    /**
     * Change emergency level. Immutable history + audit.
     *
     * Severity computed internally - downgrade detected automatically.
     * Downgrade requires non-empty $reason (model-level guarantee).
     * Admin-only downgrade enforcement = Phase 3 controller/UI.
     *
     * @param  string|null $newLevel  critical|urgent|assistance|unclassified
     * @param  int|null    $userId    NULL = system
     * @param  string|null $reason    required when downgrade detected
     * @return bool        true if changed
     */
    public function changeLevel(?string $newLevel, ?int $userId, ?string $reason = null): bool
    {
        if (!$this->canChangeLevel()) return false;

        $valid = ['critical', 'urgent', 'assistance', 'unclassified'];
        if (!in_array($newLevel, $valid, true)) return false;
        if ($newLevel === $this->emergency_level) return false;

        $oldSev = self::LEVEL_SEVERITY[$this->emergency_level] ?? null;
        $newSev = self::LEVEL_SEVERITY[$newLevel] ?? null;
        $isDowngrade = ($oldSev !== null && $newSev !== null && $newSev < $oldSev);

        if ($isDowngrade && empty($reason)) return false;

        $from = $this->emergency_level;

        $this->update([
            'emergency_level'  => $newLevel,
            'level_changed_at' => now(),
            'level_changed_by' => $userId,
        ]);

        SosLevelHistory::create([
            'sos_alert_id' => $this->id,
            'from_level'   => $from,
            'to_level'     => $newLevel,
            'changed_by'   => $userId,
            'reason'       => $reason,
            'created_at'   => now(),
        ]);

        $this->logAction(
            'level_changed', $userId, $this->status, $this->status,
            $reason ?? ('Level: ' . ($from ?? 'null') . ' -> ' . $newLevel),
            ['from_level' => $from, 'to_level' => $newLevel, 'downgrade' => $isDowngrade]
        );

        return true;
    }

    /**
     * Traveler-initiated cancel (plan 09.2).
     * Maps to existing 'false_alarm' + cancel_reason for Phase 1.
     * Admin/provider may verify - cancel != safety confirmed.
     */
    public function cancel(int $userId, string $reason): bool
    {
        if ($this->isTerminal()) return false;
        $from = $this->status;
        $this->update([
            'status'             => 'false_alarm',
            'resolved_at'        => now(),
            'resolved_by'        => $userId,
            'cancel_reason'      => $reason,
            'false_alarm_reason' => $reason,
            'is_resolved'        => true,
        ]);
        $this->logAction('cancelled', $userId, $from, 'false_alarm', $reason,
            ['cancel_reason' => $reason]);
        return true;
    }
}