<?php

namespace App\Support;

use App\Enums\BannerPlacement;
use App\Modules\Banners\Models\Banner;
use App\Modules\Pages\Models\Page;
use App\Modules\Posts\Models\Post;

class PublicContent
{
    public static function page(Page $page): array
    {
        $page->loadMissing(['sections', 'featuredImage', 'seoMeta', 'author']);

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'summary' => $page->summary,
            'content' => $page->content,
            'template' => $page->template,
            'featuredImage' => self::media($page->featuredImage),
            'sections' => $page->sections->map(fn ($s) => [
                'type' => $s->component_type,
                'settings' => $s->settings ?? [],
            ])->values()->all(),
            'author' => $page->author?->name,
            'publishedAt' => $page->published_at?->toIso8601String(),
            'seo' => PublicSeo::fromModel($page),
        ];
    }

    public static function post(Post $post): array
    {
        $post->loadMissing(['featuredImage', 'seoMeta', 'author']);

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'summary' => $post->summary,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'featuredImage' => self::media($post->featuredImage),
            'author' => $post->author?->name,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'seo' => PublicSeo::fromModel($post),
        ];
    }

    public static function postCard(Post $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?? $post->summary,
            'featuredImage' => self::media($post->featuredImage),
            'publishedAt' => $post->published_at?->format('M j, Y'),
        ];
    }

    public static function pageCard(Page $page): array
    {
        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'summary' => $page->summary,
            'featuredImage' => self::media($page->featuredImage),
        ];
    }

    public static function banner(Banner $banner): array
    {
        $banner->loadMissing(['desktopImage', 'mobileImage', 'backgroundImage']);

        return [
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'description' => $banner->description,
            'buttonText' => $banner->button_text,
            'buttonUrl' => $banner->button_url,
            'desktopImage' => self::media($banner->desktopImage),
            'mobileImage' => self::media($banner->mobileImage),
            'backgroundImage' => self::media($banner->backgroundImage),
        ];
    }

    public static function heroBanner(): ?array
    {
        $banner = Banner::published()
            ->notExpired()
            ->where('placement', BannerPlacement::HomepageHero)
            ->orderBy('sort_order')
            ->first();

        return $banner ? self::banner($banner) : null;
    }

    public static function sliderBanners(): array
    {
        return Banner::published()
            ->notExpired()
            ->where('placement', BannerPlacement::HomepageSlider)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($b) => self::banner($b))
            ->all();
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
