<?php

namespace App\Support;

use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Services\BannerRenderingService;
use App\Modules\Content\Models\Content;

class PublicContent
{
    public static function entry(Content $content): array
    {
        $content->loadMissing(['featuredImage', 'seoMeta', 'author']);
        $registry = app(ContentTypeRegistry::class);

        return [
            'id' => $content->id,
            'title' => $content->title,
            'slug' => $content->slug,
            'summary' => $content->summary,
            'body' => HtmlSanitizer::clean($content->body),
            'contentType' => [
                'key' => $content->content_type,
                'label' => $registry->label($content->content_type),
            ],
            'featuredImage' => self::media($content->featuredImage),
            'author' => $content->author?->name,
            'publishedAt' => $content->published_at?->toIso8601String(),
            'seo' => PublicSeo::fromModel($content),
        ];
    }

    public static function contentCard(Content $content): array
    {
        return [
            'title' => $content->title,
            'slug' => $content->slug,
            'summary' => $content->summary,
            'contentType' => [
                'key' => $content->content_type,
                'label' => app(ContentTypeRegistry::class)->label($content->content_type),
            ],
            'featuredImage' => self::media($content->featuredImage),
            'publishedAt' => $content->published_at?->format('M j, Y'),
        ];
    }

    public static function banner(Banner $banner): array
    {
        return app(BannerRenderingService::class)->dto($banner);
    }

    public static function bannerByKey(string $key): ?array
    {
        return app(BannerRenderingService::class)->bannerByKey($key);
    }

    public static function media($media): ?array
    {
        if (! $media) {
            return null;
        }

        return [
            'url' => $media->url(),
            'alt' => $media->alt_text ?? '',
        ];
    }
}
