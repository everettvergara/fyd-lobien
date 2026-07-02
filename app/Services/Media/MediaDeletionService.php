<?php

namespace App\Services\Media;

use App\Models\Media;

class MediaDeletionService
{
    public function __construct(
        protected MediaStorageService $storage,
        protected MediaUsageService $usage,
        protected MediaHistoryService $history,
    ) {}

    public function archive(Media $media, ?int $userId = null): Media
    {
        $media->update(['archived_at' => now()]);
        $this->history->record($media, 'archived', [], $userId);

        return $media->refresh();
    }

    public function restore(Media $media, ?int $userId = null): Media
    {
        if ($media->trashed()) {
            $media->restore();
        }

        $media->update(['archived_at' => null]);
        $this->history->record($media, 'restored', [], $userId);

        return $media->refresh();
    }

    public function softDelete(Media $media, bool $force = false, ?int $userId = null): void
    {
        if (! $force && $this->usage->isInUse($media)) {
            throw new \RuntimeException('This asset is currently in use. Confirm override before deleting it.');
        }

        $this->history->record($media, 'deleted', ['soft' => true], $userId);
        $media->delete();
    }

    public function permanentDelete(Media $media, bool $force = false, ?int $userId = null): void
    {
        if (! $force && $this->usage->isInUse($media)) {
            throw new \RuntimeException('This asset is currently in use. Confirm override before permanently deleting it.');
        }

        $media->loadMissing('variants');
        foreach ($media->variants as $variant) {
            if ($variant->path !== $media->path) {
                $this->storage->delete($variant->disk, $variant->path);
            }
        }

        $this->storage->delete($media->disk, $media->path);
        $this->history->record($media, 'permanently_deleted', [], $userId);
        $media->forceDelete();
    }
}
