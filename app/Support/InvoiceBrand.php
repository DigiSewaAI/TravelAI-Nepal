<?php

namespace App\Support;

use App\Models\Provider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Resolve invoice branding for a given provider (booking → traveler).
 *
 * Priority:
 *   1. Plan.features has "White-label"  → full rebrand (logo + name + footer + copyright)
 *   2. Plan.features has "Custom Logo"  → logo only (name/footer stay TravelAI)
 *   3. Otherwise                        → TravelAI default
 *
 * Logo resolution (DOMPDF-safe): base64 inline; fallbacks checked in order.
 */
class InvoiceBrand
{
    public const TIER_TRAVELAI  = 'travelai';
    public const TIER_LOGO_ONLY = 'logo_only';
    public const TIER_WHITE     = 'white_label';

    public static function forBookingInvoice(?Provider $provider): array
    {
        if (!$provider) {
            return self::travelaiDefault();
        }

        $plan     = $provider->activeSubscription?->plan;
        $features = $plan?->features ?? [];

        if (is_array($features) && in_array('White-label', $features, true)) {
            return self::whiteLabel($provider);
        }

        if (is_array($features) && in_array('Custom Logo', $features, true)) {
            return self::logoOnly($provider);
        }

        return self::travelaiDefault();
    }

    protected static function travelaiDefault(): array
    {
        $logo = self::encodeImage(public_path('images/logo.png'));

        return [
            'tier'           => self::TIER_TRAVELAI,
            'name'           => 'TravelAI Nepal',
            'logo_base64'    => $logo,
            'logo_mime'      => 'image/png',
            'footer_tagline' => 'TravelAI Nepal — AI + data-driven trekking ecosystem. Built for Nepal, by passion.',
            'copyright'      => '© ' . date('Y') . ' TravelAI Nepal. All rights reserved.',
            'watermark_text' => 'TravelAI Nepal',
            'watermark_show' => true,
        ];
    }

    protected static function logoOnly(Provider $provider): array
    {
        $default = self::travelaiDefault();
        $logo    = self::resolveProviderLogo($provider->logo_url);

        if (!$logo) {
            return $default;
        }

        return array_merge($default, [
            'tier'        => self::TIER_LOGO_ONLY,
            'logo_base64' => $logo['data'],
            'logo_mime'   => $logo['mime'],
            'watermark_text' => 'TravelAI Nepal',
            'watermark_show' => true,
        ]);
    }

    protected static function whiteLabel(Provider $provider): array
    {
        $default = self::travelaiDefault();
        $logo    = self::resolveProviderLogo($provider->logo_url);
        $name    = $provider->name ?: 'Provider';

        return [
            'tier'           => self::TIER_WHITE,
            'name'           => $name,
            'logo_base64'    => $logo['data'] ?? $default['logo_base64'],
            'footer_tagline' => $name . ' — powered by TravelAI Nepal',
            'copyright'      => '© ' . date('Y') . ' ' . $name . '. All rights reserved.',
            'watermark_text' => $name,
            'watermark_show' => true,
        ];
    }

    /**
     * Resolve provider logo across possible storage locations.
     * Values look like: "providers/logos/abc123.png"
     */
    protected static function resolveProviderLogo(?string $logoUrl): ?array
{
    if (!$logoUrl) {
        return null;
    }

    $clean = ltrim($logoUrl, '/');

    // 1️⃣ Try Laravel Storage (R2 / S3 / default disk) – production
    $disks = array_filter([
        config('filesystems.default'), // often 'r2' on Laravel Cloud
        'r2',
        'public',
        's3',
    ]);

    foreach (array_unique($disks) as $disk) {
        try {
            if (Storage::disk($disk)->exists($clean)) {
                $bytes = Storage::disk($disk)->get($clean);
                if ($bytes) {
                    return [
                        'data' => base64_encode($bytes),
                        'mime' => Storage::disk($disk)->mimeType($clean) ?: 'image/png',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // log silently? optional
        }
    }

    // 2️⃣ Fallback to local file paths (development)
    $candidates = [
        storage_path('app/public/' . $clean),
        public_path($clean),
        public_path('storage/' . $clean),
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            $data = self::encodeImage($path);
            if ($data !== null) {
                return [
                    'data' => $data,
                    'mime' => File::mimeType($path) ?: 'image/png',
                ];
            }
        }
    }

    return null;
}

    protected static function encodeImage(string $path, int $maxDimension = 300): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $info = @getimagesize($path);
        if ($info === false) {
            return null;
        }

        // Resize ठूलो logo — DOMPDF base64 attribute limit bypass
        if (extension_loaded('gd') && ($info[0] > $maxDimension || $info[1] > $maxDimension)) {
            $resized = self::resizeWithGd($path, $info, $maxDimension);
            if ($resized !== null) {
                return $resized;
            }
        }

        $bytes = @file_get_contents($path);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        return base64_encode($bytes);
    }

    protected static function resizeWithGd(string $path, array $info, int $maxDim): ?string
    {
        $src = match ($info[2]) {
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default        => null,
        };
        if (!$src) {
            return null;
        }

        $ratio = min($maxDim / $info[0], $maxDim / $info[1]);
        $newW  = (int) round($info[0] * $ratio);
        $newH  = (int) round($info[1] * $ratio);

        $dst = imagecreatetruecolor($newW, $newH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $info[0], $info[1]);

        ob_start();
        imagepng($dst, null, 8);
        $bytes = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return $bytes ? base64_encode($bytes) : null;
    }
}