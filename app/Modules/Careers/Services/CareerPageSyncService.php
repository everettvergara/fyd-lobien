<?php

namespace App\Modules\Careers\Services;

use App\Enums\ContentStatus;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;

class CareerPageSyncService
{
    public const INDEX_PATH = '/careers';

    public const BLOCK_TYPE = 'careers-listing';

    public function syncIndexPage(): void
    {
        if (Page::query()->where('path', self::INDEX_PATH)->exists()) {
            return;
        }

        $page = Page::create([
            'path' => self::INDEX_PATH,
            'slug' => Page::slugFromPath(self::INDEX_PATH),
            'title' => 'Careers',
            'summary' => 'Browse open job opportunities.',
            'body' => '',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $page->saveSeo([
            'seo_title' => 'Careers',
            'meta_description' => 'Browse open job opportunities and apply online.',
            'sitemap_include' => true,
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'region_key' => 'main',
            'block_type' => self::BLOCK_TYPE,
            'sort_order' => 0,
            'config' => [],
        ]);
    }

    public function removeIndexPageIfManaged(): void
    {
        $page = Page::query()
            ->where('path', self::INDEX_PATH)
            ->with('blocks')
            ->first();

        if ($page === null) {
            return;
        }

        $blocks = $page->blocks;

        if ($blocks->isEmpty()) {
            $page->delete();

            return;
        }

        $managedOnly = $blocks->every(
            fn (PageBlock $block) => $block->block_type === self::BLOCK_TYPE,
        );

        if ($managedOnly) {
            $page->delete();
        }
    }
}
