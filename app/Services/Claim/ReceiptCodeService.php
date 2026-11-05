<?php

namespace App\Services\Claim;

use RuntimeException;

class ReceiptCodeService
{
    private string $secret;
    private ?string $secretOld;
    private string $charset;
    private int $length;

    public function __construct()
    {
        $secret = (string) config('claim.code_secret', '');
        if (empty($secret)) {
            throw new RuntimeException(
                'CLAIM_CODE_SECRET is not configured. Receipt code service is disabled.'
            );
        }
        $this->secret = $secret;
        $this->secretOld = config('claim.code_secret_old');
        $this->charset = (string) config('claim.receipt_code_charset', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789');
        $this->length = (int) config('claim.receipt_code_length', 8);
    }

    public function generate(): string
    {
        $max = strlen($this->charset) - 1;
        $code = '';
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->charset[random_int(0, $max)];
        }
        return $code;
    }

    public function hash(string $plainCode): string
    {
        return hash_hmac('sha256', strtoupper($plainCode), $this->secret);
    }

    public function verify(string $plainCode, string $storedHash): bool
    {
        if (hash_equals($this->hash($plainCode), $storedHash)) {
            return true;
        }
        if (!empty($this->secretOld)) {
            $old = hash_hmac('sha256', strtoupper($plainCode), $this->secretOld);
            if (hash_equals($old, $storedHash)) {
                return true;
            }
        }
        return false;
    }
}