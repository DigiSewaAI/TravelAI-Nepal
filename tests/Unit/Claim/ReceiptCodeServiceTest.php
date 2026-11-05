<?php

namespace Tests\Unit\Claim;

use App\Services\Claim\ReceiptCodeService;
use Tests\TestCase;

class ReceiptCodeServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'claim.code_secret' => 'test-secret-key-32-characters-long',
            'claim.code_secret_old' => null,
            'claim.receipt_code_charset' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
            'claim.receipt_code_length' => 8,
        ]);
    }

    public function test_generates_eight_character_code(): void
    {
        $service = new ReceiptCodeService();
        $code = $service->generate();
        $this->assertSame(8, strlen($code));
    }

    public function test_code_uses_only_safe_charset(): void
    {
        $service = new ReceiptCodeService();
        $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        for ($i = 0; $i < 100; $i++) {
            foreach (str_split($service->generate()) as $char) {
                $this->assertStringContainsString($char, $charset);
            }
        }
    }

    public function test_code_does_not_contain_ambiguous_chars(): void
    {
        $service = new ReceiptCodeService();
        $forbidden = ['0', 'O', '1', 'I', 'l'];  // uppercase L is safe
        for ($i = 0; $i < 100; $i++) {
            $code = $service->generate();
            foreach ($forbidden as $char) {
                $this->assertStringNotContainsString($char, $code);
            }
        }
    }

    public function test_generated_codes_are_unique(): void
    {
        $service = new ReceiptCodeService();
        $codes = [];
        for ($i = 0; $i < 1000; $i++) {
            $codes[] = $service->generate();
        }
        $this->assertCount(1000, array_unique($codes));
    }

    public function test_hash_is_deterministic(): void
    {
        $service = new ReceiptCodeService();
        $this->assertSame($service->hash('ABC2XY4K'), $service->hash('ABC2XY4K'));
    }

    public function test_hash_is_case_insensitive(): void
    {
        $service = new ReceiptCodeService();
        $this->assertSame($service->hash('ABC2XY4K'), $service->hash('abc2xy4k'));
    }

    public function test_verify_returns_true_for_correct_code(): void
    {
        $service = new ReceiptCodeService();
        $hash = $service->hash('ABC2XY4K');
        $this->assertTrue($service->verify('ABC2XY4K', $hash));
    }

    public function test_verify_returns_false_for_wrong_code(): void
    {
        $service = new ReceiptCodeService();
        $hash = $service->hash('ABC2XY4K');
        $this->assertFalse($service->verify('WRONG123', $hash));
    }

    public function test_different_secret_produces_different_hash(): void
    {
        $service1 = new ReceiptCodeService();
        $hash1 = $service1->hash('ABC2XY4K');

        config(['claim.code_secret' => 'completely-different-secret-32-chars']);
        $service2 = new ReceiptCodeService();
        $hash2 = $service2->hash('ABC2XY4K');

        $this->assertNotSame($hash1, $hash2);
    }

    public function test_old_secret_verifies_rotation_grace(): void
    {
        config(['claim.code_secret' => 'old-secret-32-characters-long-here']);
        $oldHash = (new ReceiptCodeService())->hash('ABC2XY4K');

        config([
            'claim.code_secret' => 'new-secret-32-characters-long-here',
            'claim.code_secret_old' => 'old-secret-32-characters-long-here',
        ]);
        $this->assertTrue((new ReceiptCodeService())->verify('ABC2XY4K', $oldHash));
    }

    public function test_throws_when_secret_missing(): void
    {
        config(['claim.code_secret' => '']);
        $this->expectException(\RuntimeException::class);
        new ReceiptCodeService();
    }
}