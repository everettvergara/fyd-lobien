<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Services\SettingsService;
use Illuminate\Http\UploadedFile;

class MediaUploadService
{
    public function __construct(
        protected MediaStorageService $storage,
        protected MediaMetadataService $metadata,
        protected MediaVariantService $variants,
        protected MediaHistoryService $history,
        protected SettingsService $settings,
    ) {}

    public function upload(UploadedFile $file, array $attributes, int $userId): Media
    {
        $disk = $attributes['disk'] ?? $this->settings->get('media', 'disk', 'public');
        $stored = $this->storage->store($file, $disk);
        $metadata = $this->metadata->fromUploadedFile($file);

        $media = Media::create([
            ...$metadata,
            ...$stored,
            'folder_id' => $attributes['folder_id'] ?? null,
            'title' => $attributes['title'] ?? $metadata['title'],
            'alt_text' => $attributes['alt_text'] ?? null,
            'description' => $attributes['description'] ?? null,
            'caption' => $attributes['caption'] ?? null,
            'copyright' => $attributes['copyright'] ?? null,
            'credit' => $attributes['credit'] ?? null,
            'uploaded_by' => $userId,
        ]);

        if (array_key_exists('tags', $attributes)) {
            $this->metadata->syncTags($media, $attributes['tags']);
        }

        $this->variants->generateInitialVariants($media);
        $this->history->record($media, 'created', ['filename' => $media->original_filename], $userId);

        return $media->fresh(['tags', 'variants']);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, Media>
     */
    public function uploadMany(array $files, array $attributes, int $userId): array
    {
        $uploaded = [];

        foreach (array_values($files) as $file) {
            $uploaded[] = $this->upload($file, $attributes, $userId);
        }

        return $uploaded;
    }

    public function replace(Media $media, UploadedFile $file, int $userId): Media
    {
        $old = ['disk' => $media->disk, 'path' => $media->path];
        $stored = $this->storage->store($file, $media->disk);
        $metadata = $this->metadata->fromUploadedFile($file);

        $media->update([
            ...$metadata,
            ...$stored,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        $this->storage->delete($old['disk'], $old['path']);
        $this->variants->deleteVariants($media);
        $this->variants->generateInitialVariants($media);
        $this->history->record($media, 'replaced', ['old_path' => $old['path']], $userId);

        return $media->fresh(['tags', 'variants']);
    }
}
