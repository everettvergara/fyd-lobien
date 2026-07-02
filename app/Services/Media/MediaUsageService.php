<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\MediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MediaUsageService
{
    public function register(Media|int $media, Model $model, string $module, string $field, ?string $label = null): MediaUsage
    {
        $mediaId = $media instanceof Media ? $media->id : $media;

        return MediaUsage::updateOrCreate(
            [
                'media_id' => $mediaId,
                'usable_type' => $model::class,
                'usable_id' => $model->getKey(),
                'field' => $field,
            ],
            [
                'module' => $module,
                'label' => $label ?? $model->getAttribute('title') ?? $model->getAttribute('name'),
            ],
        );
    }

    public function syncModel(Model $model, string $module, array $fieldMap): void
    {
        foreach ($fieldMap as $field => $label) {
            $mediaId = $model->getAttribute($field);

            if ($mediaId) {
                $this->register((int) $mediaId, $model, $module, $field, is_string($label) ? $label : null);
                continue;
            }

            MediaUsage::where([
                'usable_type' => $model::class,
                'usable_id' => $model->getKey(),
                'field' => $field,
            ])->delete();
        }
    }

    public function syncRelatedMedia(
        Model $model,
        string $module,
        string $fieldPrefix,
        array $mediaIds,
        ?string $label = null,
    ): void {
        $existingFields = MediaUsage::query()
            ->where('usable_type', $model::class)
            ->where('usable_id', $model->getKey())
            ->pluck('field')
            ->filter(fn (string $field) => str_starts_with($field, $fieldPrefix.'_'));

        if ($existingFields->isNotEmpty()) {
            MediaUsage::query()
                ->where('usable_type', $model::class)
                ->where('usable_id', $model->getKey())
                ->whereIn('field', $existingFields->all())
                ->delete();
        }

        foreach (array_values(array_unique(array_filter($mediaIds))) as $mediaId) {
            $this->register(
                (int) $mediaId,
                $model,
                $module,
                $fieldPrefix.'_'.$mediaId,
                $label,
            );
        }
    }

    public function removeModel(Model $model): void
    {
        MediaUsage::where('usable_type', $model::class)
            ->where('usable_id', $model->getKey())
            ->delete();
    }

    public function usages(Media $media): Collection
    {
        return $media->usages()->latest()->get();
    }

    public function isInUse(Media $media): bool
    {
        return $media->usages()->exists();
    }
}
