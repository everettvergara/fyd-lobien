<?php

namespace App\Modules\Banners\Services;

use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Models\BannerTemplate;
use Illuminate\Support\Collection;

class BannerFormSchemaService
{
    public const FIELD_LABELS = [
        'headline' => 'Headline',
        'subheading' => 'Subheading',
        'description' => 'Description',
        'rich_text' => 'Rich Text',
    ];

    public const COLUMN_FIELD_LABELS = [
        'headline' => 'Title',
        'subheading' => 'Subtitle',
        'description' => 'Text',
        'rich_text' => 'Rich Text',
    ];

    public const INNER_PAGE_FIELD_LABELS = [
        'headline' => 'Title',
        'subheading' => 'Sub',
        'description' => 'Teaser',
        'rich_text' => 'Rich Text',
    ];

    public const MEDIA_LABELS = [
        'desktop_image' => 'Desktop Image',
        'tablet_image' => 'Tablet Image',
        'mobile_image' => 'Mobile Image',
        'background_image' => 'Background Image',
        'background_video' => 'Background Video',
        'poster_image' => 'Poster Image',
        'column_1_image' => 'Column 1 Picture',
        'column_2_image' => 'Column 2 Picture',
        'column_3_image' => 'Column 3 Picture',
        'column_4_image' => 'Column 4 Picture',
        'column_5_image' => 'Column 5 Picture',
        'column_6_image' => 'Column 6 Picture',
    ];

    public function defaultSchema(): array
    {
        return [
            'slides' => 1,
            'minSlides' => 1,
            'maxSlides' => 1,
            'blocks' => [
                [
                    'region' => 'main',
                    'label' => 'Content Block',
                    'fields' => ['headline', 'subheading', 'description', 'rich_text'],
                ],
            ],
            'buttons' => [
                'count' => 1,
                'styles' => ['primary', 'secondary', 'outline-primary'],
            ],
            'mediaSlots' => ['desktop_image', 'tablet_image', 'mobile_image'],
        ];
    }

    public function resolve(?BannerTemplate $template): array
    {
        $defaults = $this->defaultSchema();
        $schema = $template?->schema ?? [];

        return [
            ...$defaults,
            ...$schema,
            'blocks' => $schema['blocks'] ?? $defaults['blocks'],
            'mediaSlots' => array_key_exists('mediaSlots', $schema) ? $schema['mediaSlots'] : $defaults['mediaSlots'],
            'buttons' => array_merge($defaults['buttons'], $schema['buttons'] ?? []),
        ];
    }

    public function schemasForTemplates(Collection $templates): array
    {
        return $templates->mapWithKeys(fn (BannerTemplate $template) => [
            $template->id => $this->resolve($template),
        ])->all();
    }

    public function fieldLabel(string $field, array $blockSchema, ?string $templateKey = null): string
    {
        $labels = match (true) {
            $templateKey === 'inner_page' => self::INNER_PAGE_FIELD_LABELS,
            $this->isColumnBlock($blockSchema) => self::COLUMN_FIELD_LABELS,
            default => self::FIELD_LABELS,
        };

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    public function blockColorClass(array $blockSchema, int $blockIndex = 0): string
    {
        $region = $blockSchema['region'] ?? 'main';

        if (preg_match('/^column_(\d+)$/', $region, $matches)) {
            return 'banner-block--column-'.$matches[1];
        }

        if ($region === 'main') {
            return 'banner-block--main';
        }

        $palette = ['main', 'column-1', 'column-2', 'column-3'];

        return 'banner-block--'.($palette[$blockIndex % count($palette)] ?? 'main');
    }

    public function mediaSlotLabel(string $slot, array $blockSchema = []): string
    {
        if ($label = self::MEDIA_LABELS[$slot] ?? null) {
            return $label;
        }

        if (! empty($blockSchema['label'])) {
            return $blockSchema['label'].' Picture';
        }

        return ucwords(str_replace('_', ' ', $slot));
    }

    public function slidesFromBanner(?Banner $banner, array $schema): array
    {
        if ($banner?->slides?->isNotEmpty()) {
            return $banner->slides->map(function ($slide) use ($schema) {
                $media = $slide->mediaAssignments->keyBy('slot');

                return [
                    'name' => $slide->name,
                    'blocks' => $slide->contentBlocks->map(fn ($b) => [
                        'region' => $b->region,
                        'type' => $b->type,
                        'headline' => $b->headline,
                        'subheading' => $b->subheading,
                        'description' => $b->description,
                        'rich_text' => $b->rich_text,
                        'buttons' => $b->buttons->map(fn ($btn) => [
                            'label' => $btn->label,
                            'url' => $btn->url,
                            'target' => $btn->target,
                            'style' => $btn->style,
                        ])->values()->all(),
                    ])->values()->all(),
                    'media' => collect($this->mediaSlotsForSchema($schema))->mapWithKeys(function (string $slot) use ($media) {
                        $assignment = $media->get($slot);

                        return [$slot => [
                            'media_id' => $assignment?->media_id,
                            'url' => $assignment?->media?->url(),
                        ]];
                    })->all(),
                ];
            })->values()->all();
        }

        return $this->emptySlides($schema);
    }

    public function emptySlides(array $schema): array
    {
        $count = $this->slideCount($schema);
        $buttonCount = max(0, (int) ($schema['buttons']['count'] ?? 1));

        return collect(range(0, $count - 1))->map(fn (int $index) => [
            'name' => 'Slide '.($index + 1),
            'blocks' => collect($schema['blocks'] ?? [])->map(fn (array $block) => [
                'region' => $block['region'] ?? 'main',
                'type' => 'content',
                'headline' => null,
                'subheading' => null,
                'description' => null,
                'rich_text' => null,
                'buttons' => collect(range(0, $buttonCount - 1))->map(fn () => [
                    'label' => null,
                    'url' => null,
                    'target' => '_self',
                    'style' => 'primary',
                ])->values()->all(),
            ])->values()->all(),
            'media' => collect($this->mediaSlotsForSchema($schema))->mapWithKeys(fn (string $slot) => [
                $slot => ['media_id' => null, 'url' => null],
            ])->all(),
        ])->values()->all();
    }

    public function mediaSlotsForSchema(array $schema): array
    {
        $blockSlots = collect($schema['blocks'] ?? [])
            ->pluck('mediaSlot')
            ->filter()
            ->values()
            ->all();

        return array_values(array_unique([
            ...($schema['mediaSlots'] ?? []),
            ...$blockSlots,
        ]));
    }

    public function slideCount(array $schema): int
    {
        if (($schema['slides'] ?? 1) === 'many') {
            return max(1, (int) ($schema['maxSlides'] ?? 5));
        }

        return 1;
    }

    public function alignSlidesToSchema(array $slides, array $schema): array
    {
        $targetSlideCount = $this->slideCount($schema);
        $blockSchemas = $schema['blocks'] ?? [];
        $buttonCount = max(0, (int) ($schema['buttons']['count'] ?? 1));
        $mediaSlots = $this->mediaSlotsForSchema($schema);

        $aligned = collect($slides)->take($targetSlideCount)->values();

        while ($aligned->count() < $targetSlideCount) {
            $aligned->push([
                'name' => 'Slide '.($aligned->count() + 1),
                'blocks' => [],
                'media' => [],
            ]);
        }

        return $aligned->map(function (array $slide, int $slideIndex) use ($blockSchemas, $buttonCount, $mediaSlots) {
            $blocks = collect($slide['blocks'] ?? [])->values();

            while ($blocks->count() < count($blockSchemas)) {
                $blockSchema = $blockSchemas[$blocks->count()];
                $blocks->push([
                    'region' => $blockSchema['region'] ?? 'main',
                    'type' => 'content',
                    'headline' => null,
                    'subheading' => null,
                    'description' => null,
                    'rich_text' => null,
                    'buttons' => collect(range(0, $buttonCount - 1))->map(fn () => [
                        'label' => null,
                        'url' => null,
                        'target' => '_self',
                        'style' => 'primary',
                    ])->values()->all(),
                ]);
            }

            $media = $slide['media'] ?? [];
            foreach ($mediaSlots as $slot) {
                $media[$slot] = $media[$slot] ?? ['media_id' => null, 'url' => null];
            }

            return [
                'name' => $slide['name'] ?? 'Slide '.($slideIndex + 1),
                'blocks' => $blocks->take(count($blockSchemas))->values()->all(),
                'media' => $media,
            ];
        })->values()->all();
    }

    public function isCarouselTemplate(?BannerTemplate $template): bool
    {
        return ($template?->schema['slides'] ?? null) === 'many'
            || $template?->key === 'image_carousel';
    }

    public function supportsManySlides(array $schema): bool
    {
        return ($schema['slides'] ?? 1) === 'many';
    }

    public function isColumnBlock(array $blockSchema): bool
    {
        return str_starts_with($blockSchema['region'] ?? '', 'column_');
    }

    public function hasPerBlockMedia(array $schema): bool
    {
        return collect($schema['blocks'] ?? [])->contains(fn (array $block) => ! empty($block['mediaSlot']));
    }
}
