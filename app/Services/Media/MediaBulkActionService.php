<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MediaBulkActionService
{
    public function __construct(
        protected MediaDeletionService $deletion,
        protected MediaMetadataService $metadata,
        protected MediaHistoryService $history,
        protected MediaStorageService $storage,
        protected MediaVariantService $variants,
    ) {}

    public function apply(string $action, array $ids, array $payload = [], ?int $userId = null): array
    {
        $assets = Media::with(['tags', 'usages'])->whereIn('id', $ids)->get();

        return match ($action) {
            'delete' => $this->delete($assets, (bool) ($payload['force'] ?? false), $userId),
            'archive' => $this->archive($assets, $userId),
            'restore' => $this->restore($assets, $userId),
            'move', 'change_folder' => $this->move($assets, $payload['folder_id'] ?? null, $userId),
            'add_tags' => $this->addTags($assets, $payload['tags'] ?? [], $userId),
            'remove_tags' => $this->removeTags($assets, $payload['tags'] ?? [], $userId),
            'copy' => $this->copy($assets, $payload['folder_id'] ?? null, $userId),
            default => ['processed' => 0, 'skipped' => $assets->count(), 'message' => 'Unsupported bulk action.'],
        };
    }

    protected function delete(Collection $assets, bool $force, ?int $userId): array
    {
        $processed = 0;
        $skipped = 0;

        foreach ($assets as $asset) {
            try {
                $this->deletion->softDelete($asset, $force, $userId);
                $processed++;
            } catch (\RuntimeException) {
                $skipped++;
            }
        }

        return compact('processed', 'skipped');
    }

    protected function archive(Collection $assets, ?int $userId): array
    {
        foreach ($assets as $asset) {
            $this->deletion->archive($asset, $userId);
        }

        return ['processed' => $assets->count(), 'skipped' => 0];
    }

    protected function restore(Collection $assets, ?int $userId): array
    {
        foreach ($assets as $asset) {
            $this->deletion->restore($asset, $userId);
        }

        return ['processed' => $assets->count(), 'skipped' => 0];
    }

    protected function move(Collection $assets, ?int $folderId, ?int $userId): array
    {
        foreach ($assets as $asset) {
            $asset->update(['folder_id' => $folderId]);
            $this->history->record($asset, 'moved', ['folder_id' => $folderId], $userId);
        }

        return ['processed' => $assets->count(), 'skipped' => 0];
    }

    protected function addTags(Collection $assets, array|string $tags, ?int $userId): array
    {
        foreach ($assets as $asset) {
            $merged = $asset->tags->pluck('name')->merge(is_array($tags) ? $tags : explode(',', $tags))->all();
            $this->metadata->syncTags($asset, $merged);
            $this->history->record($asset, 'tags_added', ['tags' => $tags], $userId);
        }

        return ['processed' => $assets->count(), 'skipped' => 0];
    }

    protected function removeTags(Collection $assets, array|string $tags, ?int $userId): array
    {
        $remove = collect(is_array($tags) ? $tags : explode(',', $tags))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->map(fn ($tag) => mb_strtolower($tag))
            ->all();

        foreach ($assets as $asset) {
            $remaining = $asset->tags
                ->reject(fn ($tag) => in_array(mb_strtolower($tag->name), $remove, true))
                ->pluck('name')
                ->all();
            $this->metadata->syncTags($asset, $remaining);
            $this->history->record($asset, 'tags_removed', ['tags' => $tags], $userId);
        }

        return ['processed' => $assets->count(), 'skipped' => 0];
    }

    protected function copy(Collection $assets, ?int $folderId, ?int $userId): array
    {
        $processed = 0;

        foreach ($assets as $asset) {
            $extension = $asset->extension ? ".{$asset->extension}" : '';
            $filename = Str::uuid().$extension;
            $directory = pathinfo($asset->path, PATHINFO_DIRNAME);
            $targetPath = "{$directory}/copies/{$filename}";

            $this->storage->copy($asset->disk, $asset->path, $asset->disk, $targetPath);

            $copy = $asset->replicate(['uuid', 'filename', 'path', 'checksum', 'created_at', 'updated_at', 'deleted_at', 'archived_at']);
            $copy->fill([
                'uuid' => (string) Str::uuid(),
                'folder_id' => $folderId,
                'filename' => $filename,
                'original_filename' => pathinfo($asset->original_filename, PATHINFO_FILENAME).' (Copy)'.$extension,
                'title' => ($asset->title ?: pathinfo($asset->original_filename, PATHINFO_FILENAME)).' (Copy)',
                'path' => $targetPath,
                'uploaded_by' => $userId ?? $asset->uploaded_by,
            ]);
            $copy->save();
            $copy->tags()->sync($asset->tags->pluck('id')->all());
            $this->variants->generateInitialVariants($copy);
            $this->history->record($copy, 'created', ['copied_from' => $asset->id], $userId);
            $processed++;
        }

        return ['processed' => $processed, 'skipped' => 0];
    }
}
