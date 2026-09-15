<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class QuotaPeriod
{
    public const TZ = 'Asia/Kathmandu';

    public static function current(): string
    {
        return CarbonImmutable::now(self::TZ)->format('Y-m');
    }

    public static function forDate(\DateTimeInterface|string $date): string
    {
        return CarbonImmutable::parse($date, 'UTC')
            ->setTimezone(self::TZ)
            ->format('Y-m');
    }
}