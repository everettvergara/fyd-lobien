<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaTag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaMetadataService
{
    public function fromUploadedFile(UploadedFile $file): array
    {
        $dimensions = $this->imageDimensions($file);

        return [
            'original_filename' => $file->getClientOriginalName(),
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => strtolower($file->getClientOriginalExtension() ?: $file->extension()),
            'size' => $file->getSize() ?: 0,
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'checksum' => ($path = $file->getRealPath()) ? hash_file('sha256', $path) : null,
        ];
    }

    public function update(Media $media, array $data): Media
    {
        $media->update(collect($data)->only([
            'folder_id',
            'title',
            'description',
            'caption',
            'alt_text',
            'copyright',
            'credit',
        ])->all());

        if (array_key_exists('tags', $data)) {
            $this->syncTags($media, $data['tags'] ?? []);
        }

        return $media->refresh();
    }

    public function syncTags(Media $media, array|string|null $tags): void
    {
        $tagNames = collect(is_array($tags) ? $tags : explode(',', (string) $tags))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn ($tag) => Str::lower($tag))
            ->values();

        $tagIds = $tagNames->map(function (string $name) {
            return MediaTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            )->id;
        })->all();

        $media->tags()->sync($tagIds);
    }

    protected function imageDimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return ['width' => null, 'height' => null];
        }

        $size = @getimagesize($file->getRealPath());

        return [
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
        ];
    }
}
