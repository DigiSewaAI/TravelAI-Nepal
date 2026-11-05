<?php

namespace App\Services\Claim;

class OtpService
{
    public function generate(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function hash(string $otp): string
    {
        return hash_hmac('sha256', $otp, (string) config('app.key'));
    }

    public function verify(string $inputOtp, string $storedHash): bool
    {
        return hash_equals($this->hash($inputOtp), $storedHash);
    }

    public function expiryMinutes(): int
    {
        return (int) config('claim.otp_expiry_minutes', 5);
    }

    public function maxAttempts(): int
    {
        return (int) config('claim.otp_max_attempts', 5);
    }
}