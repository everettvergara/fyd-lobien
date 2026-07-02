<?php

namespace App\Services\Media;

use App\Models\Media;

class MediaPreviewService
{
    public function payload(Media $media): array
    {
        $media->loadMissing(['folder', 'uploader', 'tags', 'variants', 'usages', 'history.user']);

        return [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'type' => $this->typeFor($media),
            'url' => $media->url(),
            'thumbnail_url' => $media->variantUrl('thumbnail') ?? $media->url(),
            'filename' => $media->original_filename,
            'title' => $media->title,
            'description' => $media->description,
            'caption' => $media->caption,
            'alt_text' => $media->alt_text,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'size' => $media->size,
            'width' => $media->width,
            'height' => $media->height,
            'duration' => $media->duration,
            'uploaded_at' => $media->created_at?->toISOString(),
            'uploaded_by' => $media->uploader?->name,
            'tags' => $media->tags->pluck('name')->all(),
            'variants' => $media->variants->map(fn ($variant) => [
                'variant' => $variant->variant,
                'url' => $variant->url(),
                'width' => $variant->width,
                'height' => $variant->height,
                'size' => $variant->size,
            ])->values()->all(),
            'usage_count' => $media->usages->count(),
            'usages' => $media->usages->map(fn ($usage) => [
                'module' => $usage->module,
                'field' => $usage->field,
                'label' => $usage->label,
                'usable_type' => class_basename($usage->usable_type),
                'usable_id' => $usage->usable_id,
            ])->values()->all(),
            'history' => $media->history->sortByDesc('created_at')->take(20)->map(fn ($event) => [
                'action' => $event->action,
                'user' => $event->user?->name ?? 'System',
                'created_at' => $event->created_at?->format('Y-m-d H:i'),
            ])->values()->all(),
        ];
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
