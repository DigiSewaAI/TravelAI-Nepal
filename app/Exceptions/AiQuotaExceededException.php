<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * FIX-12: Thrown when an AI quota (provider monthly or guest daily)
 * has been reached.
 */
class AiQuotaExceededException extends RuntimeException
{
    public static function forProvider(int $max): self
    {
        return new self("Monthly AI limit reached ({$max}). Please upgrade your plan.");
    }

    public static function forGuest(int $max): self
    {
        return new self("Daily AI limit reached ({$max}). Please try again tomorrow.");
    }
}