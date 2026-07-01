<?php

namespace App\Support;

use App\Models\SeoMeta;

class PublicSeo
{
    public static function fromModel($model, ?string $fallbackTitle = null): array
    {
        $seo = $model->seoMeta ?? null;

        return [
            'title' => $seo?->seo_title ?? $fallbackTitle ?? $model->title ?? config('fyd.name'),
            'description' => $seo?->meta_description ?? $model->summary ?? $model->excerpt ?? '',
            'keywords' => $seo?->meta_keywords ?? '',
            'canonical' => $seo?->canonical_url ?? null,
            'robots' => $seo?->robots ?? 'index,follow',
            'og' => [
                'title' => $seo?->og_title ?? $fallbackTitle ?? $model->title ?? '',
                'description' => $seo?->og_description ?? $model->summary ?? '',
            ],
        ];
    }

    public static function defaults(?string $title = null, ?string $description = null): array
    {
        return [
            'title' => $title ?? config('fyd.name'),
            'description' => $description ?? '',
            'keywords' => '',
            'canonical' => null,
            'robots' => 'index,follow',
            'og' => ['title' => $title ?? '', 'description' => $description ?? ''],
        ];
    }
}
