<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class MediaDownloadService
{
    public function download(Media $media, ?string $variant = null): StreamedResponse
    {
        $download = $variant
            ? $media->variants()->where('variant', $variant)->first()
            : null;

        $disk = $download?->disk ?? $media->disk;
        $path = $download?->path ?? $media->path;
        $name = $variant
            ? pathinfo($media->original_filename, PATHINFO_FILENAME)."-{$variant}.".($download?->extension ?? $media->extension)
            : $media->original_filename;

        return Storage::disk($disk)->download($path, $name);
    }

    public function zip(array $mediaIds): ?string
    {
        if (! class_exists(ZipArchive::class)) {
            return null;
        }

        $media = Media::whereIn('id', $mediaIds)->get();
        if ($media->isEmpty()) {
            return null;
        }

        $relativePath = 'media/exports/media-'.now()->format('Ymd-His').'.zip';
        $absolutePath = Storage::disk('local')->path($relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0775, true);
        }

        $zip = new ZipArchive();
        $zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($media as $asset) {
            $source = Storage::disk($asset->disk)->path($asset->path);
            if (is_file($source)) {
                $zip->addFile($source, $asset->original_filename);
            }
        }

        $zip->close();

        return $absolutePath;
    }
}
