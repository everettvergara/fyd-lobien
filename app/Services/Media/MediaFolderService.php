<?php

namespace App\Services\Media;

use App\Models\MediaFolder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MediaFolderService
{
    public function tree(): Collection
    {
        return MediaFolder::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function all(): Collection
    {
        return MediaFolder::query()
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data, ?int $userId = null): MediaFolder
    {
        return MediaFolder::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'created_by' => $userId,
        ]);
    }

    public function rename(MediaFolder $folder, string $name): MediaFolder
    {
        $folder->update([
            'name' => $name,
            'slug' => $this->uniqueSlug($name, $folder->id),
        ]);

        return $folder->refresh();
    }

    public function move(MediaFolder $folder, ?int $parentId, int $sortOrder = 0): MediaFolder
    {
        if ($parentId === $folder->id || $this->isDescendant($parentId, $folder)) {
            throw new \InvalidArgumentException('A folder cannot be moved into itself or one of its descendants.');
        }

        $folder->update([
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
        ]);

        return $folder->refresh();
    }

    public function delete(MediaFolder $folder): void
    {
        $folder->delete();
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'folder';
        $slug = $base;
        $i = 2;

        while (MediaFolder::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function isDescendant(?int $candidateId, MediaFolder $folder): bool
    {
        while ($candidateId) {
            $candidate = MediaFolder::find($candidateId);
            if (! $candidate) {
                return false;
            }

            if ($candidate->parent_id === $folder->id) {
                return true;
            }

            $candidateId = $candidate->parent_id;
        }

        return false;
    }
}
