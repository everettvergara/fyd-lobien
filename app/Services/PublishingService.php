<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Support\SeoFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PublishingService
{
    public function publish(Model $model, string $module): void
    {
        $model->update([
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        ActivityLogger::log($module, 'published', $model);
    }

    public function archive(Model $model, string $module): void
    {
        $model->update(['status' => ContentStatus::Archived]);

        ActivityLogger::log($module, 'updated', $model, ['action' => 'archived']);
    }

    /**
     * @param  callable|null  $afterSave  function (Model $source, Model $duplicate): void
     */
    public function duplicate(
        Model $source,
        string $module,
        array $overrides,
        ?callable $afterSave = null,
        array $replicateExcept = ['slug', 'published_at'],
    ): Model {
        $duplicate = $source->replicate($replicateExcept);
        $duplicate->fill(array_merge([
            'status' => ContentStatus::Draft,
            'published_at' => null,
        ], $overrides));
        $duplicate->save();

        if ($afterSave) {
            $afterSave($source, $duplicate);
        }

        if (method_exists($source, 'seoMeta') && method_exists($duplicate, 'saveSeo')) {
            $source->loadMissing('seoMeta');

            if ($source->seoMeta) {
                $duplicate->saveSeo($source->seoMeta->only(array_keys(SeoFields::rules())));
            }
        }

        ActivityLogger::log($module, 'created', $duplicate, ['duplicated_from' => $source->getKey()]);

        return $duplicate;
    }

    public function generateCopySlug(string $slug): string
    {
        return $slug.'-copy-'.Str::random(4);
    }
}
