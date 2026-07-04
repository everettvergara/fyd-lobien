<?php

namespace App\Modules\PropertyListings\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ListingAssetImageProcessor
{
    protected const MAX_LONG_EDGE = 1920;

    protected const JPEG_QUALITY = 75;

    public function process(UploadedFile $file): UploadedFile
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $mime = strtolower((string) $file->getMimeType());

        if ($this->shouldPassthrough($extension, $mime)) {
            return $file;
        }

        if (! function_exists('imagecreatefromstring')) {
            return $file;
        }

        $sourcePath = $file->getRealPath();
        if (! $sourcePath || ! is_file($sourcePath)) {
            return $file;
        }

        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if (! $source) {
            return $file;
        }

        $width = imagesx($source);
        $height = imagesy($source);

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
