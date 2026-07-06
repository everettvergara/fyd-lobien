<?php

namespace App\Modules\PropertyListings\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ListingAssetImageProcessor
{
    protected const PROCESS_MEMORY_LIMIT = '512M';

    protected const MAX_LONG_EDGE = 1920;

    protected const JPEG_QUALITY = 75;

    public function process(UploadedFile $file): UploadedFile
    {
        $this->ensureProcessMemoryLimit();

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $mime = strtolower((string) $file->getMimeType());

        if ($this->shouldPassthrough($extension, $mime)) {
            return $file;
        }

        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagejpeg')) {
            return $file;
        }

        $sourcePath = $file->getRealPath();
        if (! $sourcePath || ! is_file($sourcePath)) {
            return $file;
        }

        $size = @getimagesize($sourcePath);
        if ($size === false) {
            return $file;
        }

        [$width, $height] = $size;
        if (! $this->canProcessImage((int) $width, (int) $height)) {
            return $file;
        }

        $source = $this->createImageFromPath($sourcePath, $extension, $mime);
        if (! $source) {
            return $file;
        }

        if ($width <= 0 || $height <= 0) {
            imagedestroy($source);

            return $file;
        }

        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height);
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);

        $fill = imagecolorallocate($target, 255, 255, 255);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $fill);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        $tempPath = tempnam(sys_get_temp_dir(), 'listing-asset-');
        if ($tempPath === false) {
            imagedestroy($source);
            imagedestroy($target);

            return $file;
        }

        $jpegPath = $tempPath.'.jpg';
        @unlink($tempPath);

        if (! imagejpeg($target, $jpegPath, self::JPEG_QUALITY)) {
            imagedestroy($source);
            imagedestroy($target);

            return $file;
        }

        imagedestroy($source);
        imagedestroy($target);

        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $processedName = Str::slug($basename) ?: 'listing-asset';
        $processedName .= '-'.Str::random(6).'.jpg';

        return new UploadedFile(
            $jpegPath,
            $processedName,
            'image/jpeg',
            null,
            true,
        );
    }

    protected function shouldPassthrough(string $extension, string $mime): bool
    {
        if ($extension === 'svg' || str_contains($mime, 'svg')) {
            return true;
        }

        if ($extension === 'pdf' || $mime === 'application/pdf') {
            return true;
        }

        if (! str_starts_with($mime, 'image/')) {
            return true;
        }

        return false;
    }

    /**
     * @return \GdImage|false
     */
    protected function createImageFromPath(string $path, string $extension, string $mime): \GdImage|false
    {
        return match (true) {
            in_array($extension, ['jpg', 'jpeg'], true) || $mime === 'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            $extension === 'png' || $mime === 'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            $extension === 'webp' || $mime === 'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            $extension === 'gif' || $mime === 'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : false,
            $extension === 'bmp' || $mime === 'image/bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false,
            default => false,
        };
    }

    protected function canProcessImage(int $width, int $height): bool
    {
        if ($width <= 0 || $height <= 0) {
            return false;
        }

        $memoryLimit = $this->phpSizeToBytes(ini_get('memory_limit') ?: '');
        if ($memoryLimit === null || $memoryLimit < 0) {
            return true;
        }

        [$targetWidth, $targetHeight] = $this->scaledDimensions($width, $height);
        $sourcePixels = $width * $height;
        $targetPixels = $targetWidth * $targetHeight;
        $estimatedBytes = (($sourcePixels + $targetPixels) * 5) + (24 * 1024 * 1024);
        $availableBytes = $memoryLimit - memory_get_usage(true);

        return $availableBytes > $estimatedBytes;
    }

    protected function ensureProcessMemoryLimit(): void
    {
        $current = $this->phpSizeToBytes(ini_get('memory_limit') ?: '');
        $required = $this->phpSizeToBytes(self::PROCESS_MEMORY_LIMIT);

        if ($current === null || $required === null || $current < 0 || $current >= $required) {
            return;
        }

        ini_set('memory_limit', self::PROCESS_MEMORY_LIMIT);
    }

    protected function phpSizeToBytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function scaledDimensions(int $width, int $height): array
    {
        $longEdge = max($width, $height);

        if ($longEdge <= self::MAX_LONG_EDGE) {
            return [$width, $height];
        }

        $ratio = self::MAX_LONG_EDGE / $longEdge;

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }
}
