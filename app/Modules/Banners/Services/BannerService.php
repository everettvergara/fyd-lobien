<?php

namespace App\Modules\Banners\Services;

use App\Enums\BannerType;
use App\Enums\ContentStatus;
use App\Modules\Banners\Models\BannerButton;
use App\Modules\Banners\Models\BannerContentBlock;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerMediaAssignment;
use App\Modules\Banners\Models\BannerSlide;
use App\Modules\Banners\Models\BannerTemplate;
use App\Modules\Cache\Services\PublicCacheService;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUsageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BannerService
{
    public function __construct(
        protected MediaUsageService $usage,
    ) {}

    public function create(array $validated): Banner
    {
        $banner = DB::transaction(function () use ($validated) {
            $banner = Banner::create($this->bannerPayload($validated));
            $this->syncStructure($banner, $validated);

            return $banner;
        });

        $this->syncUsage($banner->refresh());
        ActivityLogger::log('banners', 'created', $banner);
        $this->flushCache($banner);

        return $banner;
    }

    public function update(Banner $banner, array $validated): Banner
    {
        $previousKey = $banner->key;

        DB::transaction(function () use ($banner, $validated) {
            $banner->update($this->bannerPayload($validated));
            $this->syncStructure($banner, $validated);
        });

        $this->syncUsage($banner->refresh());
        ActivityLogger::log('banners', 'updated', $banner);
        $this->flushCache($banner, $previousKey);

        return $banner;
    }

    public function delete(Banner $banner): void
    {
        ActivityLogger::log('banners', 'deleted', $banner);
        $this->usage->removeModel($banner);
        $banner->delete();
        $this->flushCache($banner);
    }

    public function publish(Banner $banner): void
    {
        $banner->update(['status' => ContentStatus::Published]);

        ActivityLogger::log('banners', 'published', $banner);
        $this->flushCache($banner);
    }

    public function unpublish(Banner $banner): void
    {
        $banner->update(['status' => ContentStatus::Draft]);

        ActivityLogger::log('banners', 'updated', $banner, ['action' => 'unpublished']);
        $this->flushCache($banner);
    }

    public function archive(Banner $banner): void
    {
        $banner->update(['status' => ContentStatus::Archived]);

        ActivityLogger::log('banners', 'updated', $banner, ['action' => 'archived']);
        $this->flushCache($banner);
    }

    public function duplicate(Banner $source): Banner
    {
        $duplicate = DB::transaction(function () use ($source) {
            $source->loadMissing([
                'slides.contentBlocks.buttons',
                'slides.mediaAssignments',
                'contentBlocks.buttons',
                'mediaAssignments',
            ]);

            $copy = $source->replicate();
            $copy->name = $source->name.' (Copy)';
            $copy->key = $source->key.'-copy-'.Str::random(4);
            $copy->status = ContentStatus::Draft;
            $copy->save();

            $slideMap = [];
            $blockMap = [];

            foreach ($source->slides as $slide) {
                $newSlide = $copy->slides()->create($slide->only(['name', 'sort_order', 'settings']));
                $slideMap[$slide->id] = $newSlide->id;
            }

            foreach ($source->contentBlocks as $block) {
                $newBlock = $copy->contentBlocks()->create([
                    ...$block->only(['region', 'type', 'sort_order', 'headline', 'subheading', 'description', 'rich_text', 'settings']),
                    'banner_slide_id' => $block->banner_slide_id ? ($slideMap[$block->banner_slide_id] ?? null) : null,
                ]);
                $blockMap[$block->id] = $newBlock->id;

                foreach ($block->buttons as $button) {
                    $newBlock->buttons()->create($button->only(['label', 'url', 'target', 'style', 'icon', 'sort_order', 'settings']));
                }
            }

            foreach ($source->mediaAssignments as $assignment) {
                $copy->mediaAssignments()->create([
                    ...$assignment->only(['slot', 'media_id', 'sort_order', 'alt_text', 'title_attribute', 'aria_label', 'caption', 'settings']),
                    'banner_slide_id' => $assignment->banner_slide_id ? ($slideMap[$assignment->banner_slide_id] ?? null) : null,
                    'banner_content_block_id' => $assignment->banner_content_block_id ? ($blockMap[$assignment->banner_content_block_id] ?? null) : null,
                ]);
            }

            return $copy;
        });

        $this->syncUsage($duplicate);
        ActivityLogger::log('banners', 'created', $duplicate, ['duplicated_from' => $source->id]);

        return $duplicate;
    }

    protected function flushCache(Banner $banner, ?string $previousKey = null): void
    {
        if ($previousKey) {
            Cache::forget('banners.key.'.$previousKey);
        }

        if ($banner->key) {
            Cache::forget('banners.key.'.$banner->key);
        }

        app(PublicCacheService::class)->clearAll();
    }

    protected function syncUsage(Banner $banner): void
    {
        $banner->loadMissing('mediaAssignments');
        $this->usage->removeModel($banner);

        $legacyFields = [
            'desktop_image_id' => 'Desktop Image',
            'mobile_image_id' => 'Mobile Image',
            'background_image_id' => 'Background Image',
        ];

        foreach ($legacyFields as $field => $label) {
            if ($banner->getAttribute($field)) {
                $this->usage->register((int) $banner->getAttribute($field), $banner, 'banners', $field, $label);
            }
        }

        foreach ($banner->mediaAssignments as $assignment) {
            $this->usage->register(
                $assignment->media_id,
                $banner,
                'banners',
                'media_assignment_'.$assignment->id,
                ucwords(str_replace('_', ' ', $assignment->slot)),
            );
        }
    }

    protected function bannerPayload(array $data): array
    {
        $firstSlide = $data['slides'][0] ?? [];
        $firstBlock = $firstSlide['blocks'][0] ?? [];
        $firstButton = $firstBlock['buttons'][0] ?? [];
        $firstMedia = $firstSlide['media'] ?? [];

        return [
            'name' => $data['name'],
            'key' => $data['key'],
            'title' => $firstBlock['headline'] ?? $data['title'] ?? null,
            'subtitle' => $firstBlock['subheading'] ?? $data['subtitle'] ?? null,
            'description' => $firstBlock['description'] ?? $data['description'] ?? null,
            'type' => $this->resolveType($data),
            'template_id' => $data['template_id'] ?? BannerTemplate::query()->where('key', 'hero_center')->value('id'),
            'desktop_image_id' => $firstMedia['desktop_image']['media_id'] ?? $data['desktop_image_id'] ?? null,
            'mobile_image_id' => $firstMedia['mobile_image']['media_id'] ?? $data['mobile_image_id'] ?? null,
            'background_image_id' => $firstMedia['background_image']['media_id'] ?? $data['background_image_id'] ?? null,
            'button_text' => $firstButton['label'] ?? $data['button_text'] ?? null,
            'button_url' => $firstButton['url'] ?? $data['button_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'status' => $data['status'],
            'settings' => [
                'column_ratio' => $data['column_ratio'] ?? '50/50',
            ],
            'effect_settings' => [
                'effect' => $data['effect'] ?? 'none',
                'speed' => (int) ($data['animation_speed'] ?? 500),
                'delay' => (int) ($data['delay'] ?? 0),
                'loop' => (bool) ($data['loop'] ?? false),
                'autoplay' => (bool) ($data['autoplay'] ?? false),
            ],
        ];
    }

    public function syncStructure(Banner $banner, array $data): void
    {
        $banner->slides()->delete();

        $slides = $this->normalizedSlides($data);

        foreach ($slides as $slideIndex => $slideData) {
            $slide = $banner->slides()->create([
                'name' => $slideData['name'] ?? 'Slide '.($slideIndex + 1),
                'sort_order' => $slideIndex,
                'settings' => $slideData['settings'] ?? [],
            ]);

            foreach (($slideData['blocks'] ?? []) as $blockIndex => $blockData) {
                $block = $slide->contentBlocks()->create([
                    'banner_id' => $banner->id,
                    'region' => $blockData['region'] ?? 'main',
                    'type' => $blockData['type'] ?? 'content',
                    'sort_order' => $blockIndex,
                    'headline' => $blockData['headline'] ?? null,
                    'subheading' => $blockData['subheading'] ?? null,
                    'description' => $blockData['description'] ?? null,
                    'rich_text' => $blockData['rich_text'] ?? null,
                    'settings' => $blockData['settings'] ?? [],
                ]);

                foreach (($blockData['buttons'] ?? []) as $buttonIndex => $buttonData) {
                    if (blank($buttonData['label'] ?? null) || blank($buttonData['url'] ?? null)) {
                        continue;
                    }

                    $block->buttons()->create([
                        'label' => $buttonData['label'],
                        'url' => $buttonData['url'],
                        'target' => $buttonData['target'] ?? '_self',
                        'style' => $buttonData['style'] ?? 'primary',
                        'icon' => $buttonData['icon'] ?? null,
                        'sort_order' => $buttonIndex,
                        'settings' => [],
                    ]);
                }
            }

            $this->syncSlideMedia($banner, $slide, $slideData['media'] ?? []);
        }
    }

    protected function normalizedSlides(array $data): array
    {
        if (! empty($data['slides']) && is_array($data['slides'])) {
            $slides = array_values($data['slides']);
            $template = ! empty($data['template_id'])
                ? BannerTemplate::find($data['template_id'])
                : null;
            $schema = app(BannerFormSchemaService::class)->resolve($template);

            if (app(BannerFormSchemaService::class)->supportsManySlides($schema)) {
                $slides = array_values(array_filter($slides, fn (array $slide) => $this->slideHasContent($slide)));

                if ($slides === []) {
                    $slides = [array_values($data['slides'])[0] ?? ['name' => 'Slide 1', 'blocks' => [], 'media' => []]];
                }
            }

            return $slides;
        }

        $media = [
            'desktop_image' => ['media_id' => $data['desktop_image_id'] ?? null],
            'tablet_image' => ['media_id' => $data['tablet_image_id'] ?? null],
            'mobile_image' => ['media_id' => $data['mobile_image_id'] ?? null],
            'background_image' => ['media_id' => $data['background_image_id'] ?? null],
            'background_video' => ['media_id' => $data['background_video_id'] ?? null],
            'poster_image' => ['media_id' => $data['poster_image_id'] ?? null],
        ];

        return [[
            'name' => 'Default',
            'blocks' => [[
                'region' => 'main',
                'type' => 'content',
                'headline' => $data['title'] ?? null,
                'subheading' => $data['subtitle'] ?? null,
                'description' => $data['description'] ?? null,
                'rich_text' => $data['rich_text'] ?? null,
                'buttons' => [[
                    'label' => $data['button_text'] ?? null,
                    'url' => $data['button_url'] ?? null,
                    'target' => $data['button_target'] ?? '_self',
                    'style' => $data['button_style'] ?? 'primary',
                ]],
            ]],
            'media' => $media,
        ]];
    }

    protected function syncSlideMedia(Banner $banner, BannerSlide $slide, array $media): void
    {
        foreach ($media as $slot => $assignment) {
            $mediaId = is_array($assignment) ? ($assignment['media_id'] ?? null) : $assignment;
            if (! $mediaId) {
                continue;
            }

            BannerMediaAssignment::create([
                'banner_id' => $banner->id,
                'banner_slide_id' => $slide->id,
                'slot' => $slot,
                'media_id' => $mediaId,
                'sort_order' => 0,
                'alt_text' => $assignment['alt_text'] ?? null,
                'title_attribute' => $assignment['title_attribute'] ?? null,
                'aria_label' => $assignment['aria_label'] ?? null,
                'caption' => $assignment['caption'] ?? null,
                'settings' => [],
            ]);
        }
    }

    protected function slideHasContent(array $slide): bool
    {
        foreach ($slide['media'] ?? [] as $assignment) {
            $mediaId = is_array($assignment) ? ($assignment['media_id'] ?? null) : $assignment;
            if (! blank($mediaId)) {
                return true;
            }
        }

        foreach ($slide['blocks'] ?? [] as $block) {
            foreach (['headline', 'subheading', 'description', 'rich_text'] as $field) {
                if (! blank($block[$field] ?? null)) {
                    return true;
                }
            }

            foreach ($block['buttons'] ?? [] as $button) {
                if (! blank($button['label'] ?? null) && ! blank($button['url'] ?? null)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function resolveType(array $data): string
    {
        if (! empty($data['type'])) {
            return $data['type'];
        }

        $templateId = $data['template_id'] ?? null;
        if ($templateId) {
            $template = BannerTemplate::find($templateId);
            $schema = app(BannerFormSchemaService::class)->resolve($template);

            if (app(BannerFormSchemaService::class)->supportsManySlides($schema)) {
                return BannerType::Carousel->value;
            }
        }

        if (! empty($data['slides']) && count($data['slides']) > 1) {
            return BannerType::Carousel->value;
        }

        return BannerType::Hero->value;
    }
}
