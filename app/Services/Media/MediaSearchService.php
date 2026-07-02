<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MediaSearchService
{
    public function paginate(Request|array $input, int $perPage = 24): LengthAwarePaginator
    {
        $filters = $input instanceof Request ? $input->all() : $input;
        $query = Media::query()
            ->with(['folder', 'uploader', 'tags', 'variants'])
            ->withCount('usages');

        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc');

        return $query->paginate((int) ($filters['per_page'] ?? $perPage))->withQueryString();
    }

    public function pickerItems(array $filters = [], int $limit = 48): array
    {
        $query = Media::query()->with(['tags', 'variants']);
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc');

        return $query->limit($limit)->get()->map(fn (Media $media) => [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'url' => $media->variantUrl('thumbnail') ?? $media->url(),
            'preview_url' => $media->url(),
            'filename' => $media->original_filename,
            'title' => $media->title,
            'alt_text' => $media->alt_text,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'type' => $this->typeFor($media),
        ])->all();
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search) {
            $query->where('filename', 'like', "%{$search}%")
                ->orWhere('original_filename', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('caption', 'like', "%{$search}%")
                ->orWhere('alt_text', 'like', "%{$search}%")
                ->orWhere('mime_type', 'like', "%{$search}%")
                ->orWhereHas('tags', fn (Builder $tags) => $tags->where('name', 'like', "%{$search}%"));
        });
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['folder'])) {
            $query->where('folder_id', $filters['folder']);
        }

        if (! empty($filters['type'])) {
            $type = $filters['type'];
            if ($type === 'pdf') {
                $query->where('mime_type', 'application/pdf');
            } else {
                $query->where('mime_type', 'like', "{$type}/%");
            }
        }

        if (! empty($filters['mime_type'])) {
            $query->where('mime_type', $filters['mime_type']);
        }

        if (! empty($filters['uploaded_by'])) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn (Builder $tags) => $tags->where('slug', $filters['tag']));
        }

        if (! empty($filters['min_size'])) {
            $query->where('size', '>=', (int) $filters['min_size']);
        }

        if (! empty($filters['max_size'])) {
            $query->where('size', '<=', (int) $filters['max_size']);
        }

        if (! empty($filters['uploaded_from'])) {
            $query->whereDate('created_at', '>=', $filters['uploaded_from']);
        }

        if (! empty($filters['uploaded_to'])) {
            $query->whereDate('created_at', '<=', $filters['uploaded_to']);
        }

        if (! empty($filters['archived'])) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
    }

    protected function applySorting(Builder $query, string $sort, string $direction): void
    {
        $column = match ($sort) {
            'name' => 'original_filename',
            'modified_at' => 'updated_at',
            'size' => 'size',
            'file_type' => 'mime_type',
            default => 'created_at',
        };

        $query->orderBy($column, strtolower($direction) === 'asc' ? 'asc' : 'desc');
    }

    protected function typeFor(Media $media): string
    {
        return match (true) {
            $media->isImage() => 'image',
            $media->isVideo() => 'video',
            $media->isAudio() => 'audio',
            $media->isPdf() => 'pdf',
            default => 'file',
        };
    }
}
