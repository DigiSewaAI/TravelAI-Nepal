<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MediaOptimizationService
{
    public function optimizeImage(UploadedFile $file, int $maxWidth = 1920, int $maxHeight = 1080, int $quality = 85): array
    {
        Log::info('Optimizing image with GD', [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        // Ensure directory exists
        $mediaDir = storage_path('app/public/media');
        if (!is_dir($mediaDir)) {
            mkdir($mediaDir, 0775, true);
        }

        // Load image using GD
        $source = imagecreatefromstring(file_get_contents($file->getPathname()));
        if (!$source) {
            throw new \Exception('Failed to read image.');
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // Resize if needed
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        if ($ratio < 1) {
            $newW = (int)($width * $ratio);
            $newH = (int)($height * $ratio);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $width, $height);
            imagedestroy($source);
            $source = $resized;
            $width = $newW;
            $height = $newH;
        }

        // Save optimized
        $filename = 'media/' . Str::uuid() . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);
        imagejpeg($source, $fullPath, $quality);
        imagedestroy($source);

        // Generate thumbnail (fit 300x300)
        $thumbFilename = 'media/thumb_' . Str::uuid() . '.jpg';
        $thumbPath = storage_path('app/public/' . $thumbFilename);
        // Reload original for thumbnail to keep quality
        $thumbSource = imagecreatefromstring(file_get_contents($file->getPathname()));
        if ($thumbSource) {
            $tW = imagesx($thumbSource);
            $tH = imagesy($thumbSource);
            $size = 300;
            $scale = max($size / $tW, $size / $tH);
            $newTW = (int)($tW * $scale);
            $newTH = (int)($tH * $scale);
            $thumbResized = imagecreatetruecolor($newTW, $newTH);
            imagecopyresampled($thumbResized, $thumbSource, 0, 0, 0, 0, $newTW, $newTH, $tW, $tH);
            // Center crop
            $cropX = (int)(($newTW - $size) / 2);
            $cropY = (int)(($newTH - $size) / 2);
            $thumbFinal = imagecreatetruecolor($size, $size);
            imagecopy($thumbFinal, $thumbResized, 0, 0, $cropX, $cropY, $size, $size);
            imagejpeg($thumbFinal, $thumbPath, 70);
            imagedestroy($thumbSource);
            imagedestroy($thumbResized);
            imagedestroy($thumbFinal);
        }

        Log::info('Image optimized successfully', [
            'optimized' => $filename,
            'thumbnail' => $thumbFilename,
        ]);

        return [
            'optimized' => $filename,
            'thumbnail' => $thumbFilename,
            'metadata' => ['width' => $width, 'height' => $height],
        ];
    }

    public function optimizeVideo(UploadedFile $file): array
    {
        // For video, we still need FFmpeg. But for now, just store a placeholder.
        // We'll skip video optimization for simplicity.
        throw new \Exception('Video optimization not implemented in this version.');
    }
}