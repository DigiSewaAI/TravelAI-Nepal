<?php

namespace Tests\Unit\Claim;

use App\Services\Claim\OtpService;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    public function test_generates_six_digit_otp(): void
    {
        $service = new OtpService();
        $otp = $service->generate();
        $this->assertSame(6, strlen($otp));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    public function test_leading_zeros_are_preserved(): void
    {
        $service = new OtpService();
        $found = false;
        for ($i = 0; $i < 1000; $i++) {
            if ($service->generate()[0] === '0') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected at least one OTP with leading zero');
    }

    public function test_hash_is_deterministic(): void
    {
        $service = new OtpService();
        $this->assertSame($service->hash('123456'), $service->hash('123456'));
    }

    public function test_verify_returns_true_for_correct_otp(): void
    {
        $service = new OtpService();
        $hash = $service->hash('123456');
        $this->assertTrue($service->verify('123456', $hash));
    }

    public function test_verify_returns_false_for_wrong_otp(): void
    {
        $service = new OtpService();
        $hash = $service->hash('123456');
        $this->assertFalse($service->verify('654321', $hash));
    }

    public function test_config_values_read_correctly(): void
    {
        config(['claim.otp_expiry_minutes' => 10, 'claim.otp_max_attempts' => 3]);
        $service = new OtpService();
        $this->assertSame(10, $service->expiryMinutes());
        $this->assertSame(3, $service->maxAttempts());
    }
}