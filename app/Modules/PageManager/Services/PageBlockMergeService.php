<?php

namespace App\Modules\PageManager\Services;

use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Models\PageMasterBlock;

class PageBlockMergeService
{
    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function mergedBlocksForPage(Page $page): array
    {
        $master = PageMaster::instance();
        $masterBlocks = $master->blocks()->get()->groupBy('region_key');
        $pageBlocks = $page->blocks()->get()->groupBy('region_key');
        $regions = $masterBlocks->keys()->merge($pageBlocks->keys())->unique()->values();
        $merged = [];

        foreach ($regions as $regionKey) {
            $pageRegionBlocks = $pageBlocks->get($regionKey);

            if ($pageRegionBlocks !== null && $pageRegionBlocks->isNotEmpty()) {
                $merged[$regionKey] = $this->mapBlocks($pageRegionBlocks);

                continue;
            }

            $masterRegionBlocks = $masterBlocks->get($regionKey);

            if ($masterRegionBlocks !== null && $masterRegionBlocks->isNotEmpty()) {
                $merged[$regionKey] = $this->mapBlocks($masterRegionBlocks);
            }
        }

        return $merged;
    }

    /**
     * @param  iterable<int, PageBlock|PageMasterBlock>  $blocks
     * @return array<int, array<string, mixed>>
     */
    protected function mapBlocks(iterable $blocks): array
    {
        $rows = [];

        foreach ($blocks as $block) {
            $rows[] = [
                'id' => $block->id,
                'block_type' => $block->block_type,
                'sort_order' => $block->sort_order,
                'config' => $block->config ?? [],
            ];
        }

        usort($rows, fn (array $a, array $b) => $a['sort_order'] <=> $b['sort_order']);

        return $rows;
    }
}
