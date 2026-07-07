<?php

namespace App\Support;

use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Services\BannerRenderingService;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Modules\Content\Services\ContentTypeListingService;
use App\Modules\Content\Services\ContentUrlService;
use App\Models\Media;

class PublicContent
{
    public static function entry(Content $content): array
    {
        $content->loadMissing(['featuredImage', 'attachment', 'seoMeta', 'author']);
        $registry = app(ContentTypeRegistry::class);
        $path = app(ContentUrlService::class)->pathFor($content);

        return [
            'id' => $content->id,
            'title' => $content->title,
            'slug' => $content->slug,
            'path' => $path,
            'summary' => $content->summary,
            'body' => HtmlSanitizer::clean($content->body),
            'urlLink' => $content->url_link,
            'contentType' => [
                'key' => $content->content_type,
                'label' => $registry->label($content->content_type),
            ],
            'featuredImage' => self::media($content->featuredImage),
            'attachment' => self::file($content->attachment),
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
            'path' => app(ContentUrlService::class)->pathFor($content),
            'summary' => $content->summary,
            'contentType' => [
                'key' => $content->content_type,
                'label' => app(ContentTypeRegistry::class)->label($content->content_type),
            ],
            'featuredImage' => self::media($content->featuredImage),
            'urlLink' => $content->url_link,
            'attachment' => self::file($content->attachment),
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

    public static function contentBlockByKey(string $key, int $page = 1): ?array
    {
        return app(\App\Modules\ContentBlocks\Services\ContentBlockRenderingService::class)
            ->contentBlockByKey($key, $page);
    }

    public static function contentTypeListing(ContentType $type, int $page = 1, ?string $queryParam = null, ?int $perPage = null): ?array
    {
        return app(ContentTypeListingService::class)->dto($type, $page, $queryParam, $perPage);
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

    /**
     * @return array{url: string, alt: string, srcset: string, sizes: string, previewUrl: string, width: ?int, height: ?int}|null
     */
    public static function responsiveMedia(?Media $media, string $sizes = '(max-width: 768px) 100vw, 100vw'): ?array
    {
        if (! $media) {
            return null;
        }

        $media->loadMissing('variants');

        $srcsetVariants = ['thumbnail', 'small', 'medium', 'large', 'original'];
        $fallbackVariants = ['medium', 'large', 'small', 'thumbnail', 'original'];
        $defaultWidths = [
            'thumbnail' => 300,
            'small' => 640,
            'medium' => 1024,
            'large' => 1600,
        ];

        $srcsetParts = [];

        foreach ($srcsetVariants as $variant) {
            $url = $media->variantUrl($variant);

            if ($url === null) {
                continue;
            }

            $record = $media->variants->firstWhere('variant', $variant);
            $width = $record?->width ?? ($variant === 'original' ? $media->width : ($defaultWidths[$variant] ?? null));

            if ($width) {
                $srcsetParts[$width.'w:'.$url] = $url.' '.$width.'w';
            }
        }

        $fallbackUrl = null;
        $fallbackRecord = null;

        foreach ($fallbackVariants as $variant) {
            $url = $media->variantUrl($variant);

            if ($url === null) {
                continue;
            }

            $fallbackUrl = $url;
            $fallbackRecord = $media->variants->firstWhere('variant', $variant);

            break;
        }

        $fallbackUrl ??= $media->url();
        $previewUrl = $media->variantUrl('thumbnail')
            ?? $media->variantUrl('small')
            ?? $fallbackUrl;

        return [
            'url' => $fallbackUrl,
            'alt' => $media->alt_text ?? '',
            'srcset' => implode(', ', array_values($srcsetParts)),
            'sizes' => $sizes,
            'previewUrl' => $previewUrl,
            'width' => $fallbackRecord?->width ?? $media->width,
            'height' => $fallbackRecord?->height ?? $media->height,
        ];
    }

    public static function file($media): ?array
    {
        if (! $media) {
            return null;
        }

        return [
            'url' => $media->url(),
            'label' => $media->displayName(),
            'mimeType' => $media->mime_type,
        ];
    }
}
