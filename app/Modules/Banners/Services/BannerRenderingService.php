<?php

namespace App\Modules\Banners\Services;

use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerMediaAssignment;
use Illuminate\Support\Facades\Cache;

class BannerRenderingService
{
    public function bannerByKey(string $key): ?array
    {
        return Cache::remember('banners.key.'.$key, now()->addMinutes(10), function () use ($key) {
            $banner = Banner::query()
                ->with([
                    'template',
                    'slides.contentBlocks.buttons',
                    'slides.mediaAssignments.media.variants',
                ])
                ->published()
                ->where('key', $key)
                ->first();

            return $banner ? $this->dto($banner) : null;
        });
    }

    public function dto(Banner $banner): array
    {
        $banner->loadMissing([
            'template',
            'desktopImage.variants',
            'mobileImage.variants',
            'backgroundImage.variants',
            'slides.contentBlocks.buttons',
            'slides.mediaAssignments.media.variants',
        ]);

        $slides = $banner->slides->map(fn ($slide) => [
            'id' => $slide->id,
            'name' => $slide->name,
            'settings' => $slide->settings ?? [],
            'blocks' => $slide->contentBlocks->map(fn ($block) => [
                'id' => $block->id,
                'region' => $block->region,
                'type' => $block->type,
                'headline' => $block->headline,
                'subheading' => $block->subheading,
                'description' => $block->description,
                'richText' => $block->rich_text,
                'settings' => $block->settings ?? [],
                'buttons' => $block->buttons->map(fn ($button) => [
                    'id' => $button->id,
                    'label' => $button->label,
                    'url' => $button->url,
                    'target' => $button->target,
                    'style' => $button->style,
                    'icon' => $button->icon,
                ])->values()->all(),
            ])->values()->all(),
            'media' => $slide->mediaAssignments
                ->groupBy('slot')
                ->map(fn ($items) => $this->mediaAssignment($items->first()))
                ->all(),
        ])->values()->all();

        $firstBlock = $slides[0]['blocks'][0] ?? [];
        $firstMedia = $slides[0]['media'] ?? [];
        $firstButton = $firstBlock['buttons'][0] ?? [];

        return [
            'id' => $banner->id,
            'name' => $banner->name,
            'key' => $banner->key,
            'template' => [
                'key' => $banner->template?->key ?? 'hero_center',
                'name' => $banner->template?->name ?? 'Hero Center',
                'settings' => $banner->template?->default_settings ?? [],
            ],
            'status' => $banner->status instanceof \BackedEnum ? $banner->status->value : $banner->status,
            'settings' => $banner->settings ?? [],
            'effects' => $banner->effect_settings ?? [],
            'slides' => $slides,

            // Legacy-friendly aliases used by existing components.
            'title' => $firstBlock['headline'] ?? $banner->title,
            'subtitle' => $firstBlock['subheading'] ?? $banner->subtitle,
            'description' => $firstBlock['description'] ?? $banner->description,
            'buttonText' => $firstButton['label'] ?? $banner->button_text,
            'buttonUrl' => $firstButton['url'] ?? $banner->button_url,
            'desktopImage' => $firstMedia['desktop_image'] ?? $this->legacyMedia($banner->desktopImage),
            'mobileImage' => $firstMedia['mobile_image'] ?? $this->legacyMedia($banner->mobileImage),
            'backgroundImage' => $firstMedia['background_image'] ?? $this->legacyMedia($banner->backgroundImage),
        ];
    }

    protected function mediaAssignment(?BannerMediaAssignment $assignment): ?array
    {
        if (! $assignment?->media) {
            return null;
        }

        return [
            'id' => $assignment->media->id,
            'url' => $assignment->media->url(),
            'thumbnailUrl' => $assignment->media->variantUrl('thumbnail') ?? $assignment->media->url(),
            'alt' => $assignment->alt_text ?? $assignment->media->alt_text ?? '',
            'title' => $assignment->title_attribute ?? $assignment->media->title,
            'ariaLabel' => $assignment->aria_label,
            'caption' => $assignment->caption ?? $assignment->media->caption,
            'mimeType' => $assignment->media->mime_type,
            'width' => $assignment->media->width,
            'height' => $assignment->media->height,
        ];
    }

    protected function legacyMedia($media): ?array
    {
        if (! $media) {
            return null;
        }

        return [
            'id' => $media->id,
            'url' => $media->url(),
            'thumbnailUrl' => $media->variantUrl('thumbnail') ?? $media->url(),
            'alt' => $media->alt_text ?? '',
            'title' => $media->title,
            'caption' => $media->caption,
            'mimeType' => $media->mime_type,
            'width' => $media->width,
            'height' => $media->height,
        ];
    }
}
