<?php

namespace App\Modules\Banners\Database\Seeders;

use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use Illuminate\Database\Seeder;

/**
 * Seeds system banner layout templates for new installs.
 *
 * @see docs/SEEDING.md
 */
class BannerTemplateSeeder extends Seeder
{
    protected const REMOVED_TEMPLATE_KEYS = [
        'hero_carousel',
        'fullscreen_hero',
        'card_overlay',
    ];

    public function run(): void
    {
        foreach ($this->templates() as $index => $template) {
            BannerTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    ...$template,
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }

        $fallbackTemplateId = BannerTemplate::query()->where('key', 'hero_center')->value('id');
        $removedTemplateIds = BannerTemplate::query()
            ->whereIn('key', self::REMOVED_TEMPLATE_KEYS)
            ->pluck('id');

        if ($fallbackTemplateId && $removedTemplateIds->isNotEmpty()) {
            Banner::query()
                ->whereIn('template_id', $removedTemplateIds)
                ->update(['template_id' => $fallbackTemplateId]);
        }

        BannerTemplate::query()
            ->whereIn('key', self::REMOVED_TEMPLATE_KEYS)
            ->delete();
    }

    protected function templates(): array
    {
        $heroBlock = [
            'region' => 'main',
            'label' => 'Content Block',
            'fields' => ['headline', 'subheading', 'description', 'rich_text'],
        ];

        $carouselSlideBlock = [
            'region' => 'main',
            'label' => 'Slide Content',
            'fields' => ['headline', 'subheading', 'description'],
        ];

        $heroMedia = ['desktop_image', 'tablet_image', 'mobile_image'];
        $heroButtons = ['count' => 1, 'styles' => ['primary', 'secondary', 'outline-primary']];
        $columnButtons = ['count' => 1, 'styles' => ['primary', 'secondary', 'outline-primary']];
        $carouselButtons = ['count' => 1, 'styles' => ['primary', 'secondary', 'outline-primary']];

        $columnBlock = fn (int $number): array => [
            'region' => "column_{$number}",
            'label' => "Column {$number}",
            'fields' => ['headline', 'subheading', 'description'],
            'mediaSlot' => "column_{$number}_image",
        ];

        return [
            ['key' => 'hero_center', 'name' => 'Hero Center', 'description' => 'Centered hero presentation.', 'category' => 'hero', 'schema' => ['alignment' => 'center', 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => $heroMedia], 'default_settings' => ['container' => 'full_width']],
            ['key' => 'hero_left', 'name' => 'Hero Left', 'description' => 'Left-aligned hero presentation.', 'category' => 'hero', 'schema' => ['alignment' => 'left', 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => $heroMedia], 'default_settings' => ['container' => 'full_width']],
            ['key' => 'hero_right', 'name' => 'Hero Right', 'description' => 'Right-aligned hero presentation.', 'category' => 'hero', 'schema' => ['alignment' => 'right', 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => $heroMedia], 'default_settings' => ['container' => 'full_width']],
            ['key' => 'image_carousel', 'name' => 'Image Carousel', 'description' => 'Up to 5 slides with image, title, subtitle, text, and CTA.', 'category' => 'carousel', 'schema' => ['slides' => 'many', 'minSlides' => 1, 'maxSlides' => 5, 'blocks' => [$carouselSlideBlock], 'buttons' => $carouselButtons, 'mediaSlots' => ['desktop_image']], 'default_settings' => ['container' => 'full_width', 'autoplay' => true]],
            ['key' => 'split_layout', 'name' => 'Split Layout', 'description' => 'Two-pane banner layout.', 'category' => 'layout', 'schema' => ['columns' => 2, 'ratio' => '50/50', 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => ['desktop_image', 'mobile_image']], 'default_settings' => ['container' => 'full_width']],
            ['key' => 'video_hero', 'name' => 'Video Hero', 'description' => 'Hero with background video.', 'category' => 'video', 'schema' => ['requires_video' => true, 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => ['background_video', 'poster_image', 'desktop_image', 'mobile_image']], 'default_settings' => ['container' => 'full_width']],
            ['key' => 'minimal', 'name' => 'Minimal', 'description' => 'Minimal content-first banner.', 'category' => 'hero', 'schema' => ['minimal' => true, 'slides' => 1, 'blocks' => [['region' => 'main', 'label' => 'Content Block', 'fields' => ['headline', 'description']]], 'buttons' => $heroButtons, 'mediaSlots' => []], 'default_settings' => ['container' => 'container']],
            ['key' => 'image_left', 'name' => 'Image Left', 'description' => 'Image left with content right.', 'category' => 'layout', 'schema' => ['media_position' => 'left', 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => ['desktop_image', 'mobile_image']], 'default_settings' => ['container' => 'container']],
            ['key' => 'image_right', 'name' => 'Image Right', 'description' => 'Image right with content left.', 'category' => 'layout', 'schema' => ['media_position' => 'right', 'slides' => 1, 'blocks' => [$heroBlock], 'buttons' => $heroButtons, 'mediaSlots' => ['desktop_image', 'mobile_image']], 'default_settings' => ['container' => 'container']],
            ['key' => 'two_column_full_width', 'name' => 'Two-Column Full Width', 'description' => 'Two independent full-width columns.', 'category' => 'layout', 'schema' => ['columns' => 2, 'ratios' => ['50/50', '40/60', '60/40'], 'slides' => 1, 'blocks' => [$columnBlock(1), $columnBlock(2)], 'buttons' => $columnButtons, 'mediaSlots' => []], 'default_settings' => ['container' => 'full_width', 'ratio' => '50/50']],
            ['key' => 'three_column_full_width', 'name' => 'Three-Column Full Width', 'description' => 'Three independent responsive columns.', 'category' => 'layout', 'schema' => ['columns' => 3, 'stack_mobile' => true, 'slides' => 1, 'blocks' => [$columnBlock(1), $columnBlock(2), $columnBlock(3)], 'buttons' => $columnButtons, 'mediaSlots' => []], 'default_settings' => ['container' => 'full_width']],
            ['key' => 'inner_page', 'name' => 'Inner Page Banner', 'description' => 'Compact full-width page header with optional background image, title, sub, and teaser text.', 'category' => 'section', 'schema' => ['compact' => true, 'slides' => 1, 'blocks' => [['region' => 'main', 'label' => 'Page Header', 'fields' => ['headline', 'subheading', 'description']]], 'buttons' => ['count' => 0, 'styles' => []], 'mediaSlots' => ['background_image']], 'default_settings' => ['container' => 'full_width', 'height' => 200]],
        ];
    }
}
