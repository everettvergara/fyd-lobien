<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaVariantService
{
    protected array $imageSizes = [
        'thumbnail' => 300,
        'small' => 640,
        'medium' => 1024,
        'large' => 1600,
    ];

    public function generateInitialVariants(Media $media): void
    {
        $this->recordOriginal($media);

        if (! $media->isImage()) {
            return;
        }

        foreach ($this->imageSizes as $variant => $maxSize) {
            $this->generateResizedImage($media, $variant, $maxSize);
        }

        $this->generateConvertedImage($media, 'webp', 'image/webp');
        $this->generateConvertedImage($media, 'avif', 'image/avif');
    }

    public function deleteVariants(Media $media): void
    {
        foreach ($media->variants as $variant) {
            if ($variant->variant !== 'original') {
                Storage::disk($variant->disk)->delete($variant->path);
            }
        }

        $media->variants()->delete();
    }

    protected function recordOriginal(Media $media): MediaVariant
    {
        return $media->variants()->updateOrCreate(
            ['variant' => 'original'],
            [
                'disk' => $media->disk,
                'storage_provider' => $media->storage_provider,
                'path' => $media->path,
                'mime_type' => $media->mime_type,
                'extension' => $media->extension,
                'size' => $media->size,
                'width' => $media->width,
                'height' => $media->height,
            ],
        );
    }

    protected function generateResizedImage(Media $media, string $variant, int $maxSize): void
    {
        if (! function_exists('imagecreatefromstring')) {
            return;
        }

        $sourcePath = $this->localPath($media->disk, $media->path);
        if (! $sourcePath || ! is_file($sourcePath)) {
            return;
        }

        [$width, $height] = [$media->width, $media->height];
        if (! $width || ! $height || max($width, $height) <= $maxSize) {
            return;
        }

        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if (! $source) {
            return;
        }

        $ratio = min($maxSize / $width, $maxSize / $height);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $extension = in_array($media->extension, ['png', 'gif', 'webp'], true) ? $media->extension : 'jpg';
        $path = $this->variantPath($media, $variant, $extension);
        $targetPath = $this->localPath($media->disk, $path, createDirectory: true);

        if (! $targetPath || ! $this->writeImage($target, $targetPath, $extension)) {
            imagedestroy($source);
            imagedestroy($target);
            return;
        }

        $this->recordVariant($media, $variant, $path, $media->mime_type, $extension, $targetWidth, $targetHeight);

        imagedestroy($source);
        imagedestroy($target);
    }

    protected function generateConvertedImage(Media $media, string $extension, string $mimeType): void
    {
        $function = "image{$extension}";
        if (! function_exists('imagecreatefromstring') || ! function_exists($function)) {
            return;
        }

        $sourcePath = $this->localPath($media->disk, $media->path);
        if (! $sourcePath || ! is_file($sourcePath)) {
            return;
        }

        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if (! $source) {
            return;
        }

        $path = $this->variantPath($media, $extension, $extension);
        $targetPath = $this->localPath($media->disk, $path, createDirectory: true);

        if ($targetPath && $this->writeImage($source, $targetPath, $extension)) {
            $this->recordVariant($media, $extension, $path, $mimeType, $extension, $media->width, $media->height);
        }

        imagedestroy($source);
    }

    protected function recordVariant(Media $media, string $variant, string $path, string $mimeType, string $extension, ?int $width, ?int $height): void
    {
        $media->variants()->updateOrCreate(
            ['variant' => $variant],
            [
                'disk' => $media->disk,
                'storage_provider' => $media->storage_provider,
                'path' => $path,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size' => Storage::disk($media->disk)->size($path),
                'width' => $width,
                'height' => $height,
            ],
        );
    }

    protected function variantPath(Media $media, string $variant, string $extension): string
    {
        $base = pathinfo($media->path, PATHINFO_DIRNAME);

        return "{$base}/variants/{$media->uuid}-{$variant}-".Str::random(6).".{$extension}";
    }

    protected function localPath(string $disk, string $path, bool $createDirectory = false): ?string
    {
        $adapter = Storage::disk($disk);

        if (! method_exists($adapter, 'path')) {
            return null;
        }

        $localPath = $adapter->path($path);

        if ($createDirectory && ! is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0775, true);
        }

        return $localPath;
    }

    protected function writeImage($image, string $path, string $extension): bool
    {
        return match ($extension) {
            'png' => imagepng($image, $path),
            'gif' => imagegif($image, $path),
            'webp' => imagewebp($image, $path, 82),
            'avif' => function_exists('imageavif') && imageavif($image, $path, 70),
            default => imagejpeg($image, $path, 85),
        };
    }
}
