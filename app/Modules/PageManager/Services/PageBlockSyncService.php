<?php

namespace App\Modules\PageManager\Services;

use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Models\PageMasterBlock;

class PageBlockSyncService
{
    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function syncPageBlocks(Page $page, array $blocks): void
    {
        $page->blocks()->delete();

        foreach ($blocks as $index => $block) {
            PageBlock::create([
                'page_id' => $page->id,
                'region_key' => (string) ($block['region_key'] ?? 'main'),
                'block_type' => (string) ($block['block_type'] ?? ''),
                'sort_order' => (int) ($block['sort_order'] ?? $index),
                'config' => is_array($block['config'] ?? null) ? $block['config'] : [],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    public function syncMasterBlocks(PageMaster $master, array $blocks): void
    {
        $master->blocks()->delete();

        foreach ($blocks as $index => $block) {
            PageMasterBlock::create([
                'page_master_id' => $master->id,
                'region_key' => (string) ($block['region_key'] ?? 'main'),
                'block_type' => (string) ($block['block_type'] ?? ''),
                'sort_order' => (int) ($block['sort_order'] ?? $index),
                'config' => is_array($block['config'] ?? null) ? $block['config'] : [],
            ]);
        }
    }
}
