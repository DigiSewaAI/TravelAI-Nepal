<?php

use Illuminate\Support\Carbon;

if (!function_exists('local_time')) {
    /**
     * Convert datetime to viewer's timezone for display.
     * Use ONLY for datetime fields (not date-only).
     */
    function local_time($datetime, string $format = 'M d, Y h:i A'): string
    {
        if (!$datetime) return '—';
        try {
            $dt = $datetime instanceof \DateTimeInterface
                ? Carbon::instance($datetime)
                : Carbon::parse($datetime);
        } catch (\Throwable $e) {
            return '—';
        }
        $tz = view()->shared('userTimezone') ?? config('app.timezone', 'UTC');
        return $dt->copy()->setTimezone($tz)->format($format);
    }
}

if (!function_exists('local_iso')) {
    /**
     * ISO 8601 UTC string for JS (always ends with Z).
     * Use for data-ago, JS charts, live clocks.
     */
    function local_iso($datetime): string
    {
        if (!$datetime) return '';
        try {
            $dt = $datetime instanceof \DateTimeInterface
                ? Carbon::instance($datetime)
                : Carbon::parse($datetime);
        } catch (\Throwable $e) {
            return '';
        }
        return $dt->copy()->utc()->toIso8601ZuluString();
    }
}

if (!function_exists('user_timezone')) {
    function user_timezone(): string
    {
        return view()->shared('userTimezone') ?? config('app.timezone', 'UTC');
    }
}