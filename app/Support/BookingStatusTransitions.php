<?php

namespace App\Support;

final class BookingStatusTransitions
{
    public const MAP = [
        'pending'   => ['confirmed', 'cancelled', 'rejected'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
        'rejected'  => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::MAP[$from] ?? [], true);
    }
}