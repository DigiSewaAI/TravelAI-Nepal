<?php

namespace App\Services;

use App\Models\Provider;

class NotificationGuardService
{
    /**
     * Self-notify guard: returns true if action should be SKIPPED
     * (i.e., the actor is the same provider who would receive notification)
     */
    public static function shouldSkip(Provider $provider, ?int $actorUserId): bool
    {
        if (!$actorUserId) return false;
        return (int) $provider->user_id === (int) $actorUserId;
    }

    /**
     * Guard wrapper: send notification ONLY if not self-action.
     * $sendFn: callable that sends the notification.
     */
    public static function ifNotSelf(Provider $provider, ?int $actorUserId, callable $sendFn): void
    {
        if (self::shouldSkip($provider, $actorUserId)) return;
        $sendFn();
    }
}