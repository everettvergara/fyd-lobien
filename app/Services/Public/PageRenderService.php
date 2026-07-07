<?php

namespace App\Services\Public;

use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Services\PageBlockMergeService;
use App\Services\Theme\ThemeService;
use App\Support\PublicSeo;
use App\Support\HtmlSanitizer;

class PageRenderService
{
    public function __construct(
        protected PageBlockMergeService $blockMerge,
        protected PublicBlockRegistry $blocks,
        protected ThemeService $themes,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function render(Page $page): array
    {
        $page->loadMissing(['seoMeta', 'featuredImage']);
        $merged = $this->blockMerge->mergedBlocksForPage($page);
        $regions = [];

        foreach ($merged as $regionKey => $blockRows) {
            $regions[$regionKey] = [];

            foreach ($blockRows as $index => $row) {
                $type = (string) ($row['block_type'] ?? '');
                $config = is_array($row['config'] ?? null) ? $row['config'] : [];

                if ($this->blocks->find($type) === null) {
                    continue;
                }

                $component = $this->blocks->componentFor($type);
                $props = $this->blocks->resolveProps($type, $config, $page);

                if ($component === null || $this->shouldSkipBlock($type, $props)) {
                    continue;
                }

                $regions[$regionKey][] = [
                    'id' => $row['id'] ?? "{$regionKey}-{$index}",
                    'type' => $type,
                    'component' => $component,
                    'props' => $props,
                ];
            }

            if ($regions[$regionKey] === []) {
                unset($regions[$regionKey]);
            }
        }

        $regionOrder = $this->regionOrderFor($regions);

        return [
            'page' => [
                'id' => $page->id,
                'path' => $page->path,
                'title' => $page->title,
                'slug' => $page->slug,
                'summary' => $page->summary,
                'body' => HtmlSanitizer::clean($page->body ?? ''),
                'featuredImage' => $this->media($page->featuredImage),
            ],
            'regionOrder' => $regionOrder,
            'regions' => $regions,
            'seo' => PublicSeo::fromModel($page, $page->title),
        ];
    }

    /**
     * @param  array<string, array<int, mixed>>  $regions
     * @return array<int, string>
     */
    protected function regionOrderFor(array $regions): array
    {
        $themeOrder = $this->themes->regionKeysForSlug($this->themes->activeSlug());
        $ordered = array_values(array_filter($themeOrder, fn (string $key) => isset($regions[$key])));
        $extra = array_diff(array_keys($regions), $ordered);

        return array_values(array_unique([...$ordered, ...$extra]));
    }

    /**
     * @param  array<string, mixed>  $props
     */
    protected function shouldSkipBlock(string $type, array $props): bool
    {
        return match ($type) {
            'newsletter', 'webform' => ($props['slug'] ?? '') === '',
            'property-listing-detail' => ($props['listing'] ?? null) === null,
            default => false,
        };
    }

    protected function media($media): ?array
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
