<?php

namespace App\Services\Theme;

use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PageManager\Models\PageMasterBlock;
use Illuminate\Database\Eloquent\Model;

class ThemeBlockMigrationService
{
    public function __construct(
        protected ThemeService $themes,
    ) {}

    /**
     * @return array{preserved: int, remapped: int, details: array<int, string>}
     */
    public function migrate(string $fromSlug, string $toSlug): array
    {
        if ($fromSlug === $toSlug) {
            return ['preserved' => 0, 'remapped' => 0, 'details' => []];
        }

        $newKeys = $this->themes->regionKeysForSlug($toSlug);
        $regionMap = $this->themes->regionMapForSlug($toSlug);

        if ($newKeys === []) {
            return ['preserved' => 0, 'remapped' => 0, 'details' => []];
        }

        $preserved = 0;
        $remapped = 0;
        $details = [];

        foreach ([PageBlock::query(), PageMasterBlock::query()] as $query) {
            foreach ($query->get() as $block) {
                $oldKey = (string) $block->region_key;

                if (in_array($oldKey, $newKeys, true)) {
                    $preserved++;

                    continue;
                }

                $targetKey = $this->resolveTargetRegion($oldKey, $newKeys, $regionMap);

                if ($targetKey === null) {
                    continue;
                }

                $block->region_key = $targetKey;
                $block->save();
                $remapped++;
                $details[] = "{$oldKey} → {$targetKey}";
            }
        }

        $this->reindexSortOrders(PageBlock::class, 'page_id');
        $this->reindexSortOrders(PageMasterBlock::class, 'page_master_id');

        return [
            'preserved' => $preserved,
            'remapped' => $remapped,
            'details' => $details,
        ];
    }

    /**
     * @param  array<int, string>  $newKeys
     * @param  array<string, string>  $regionMap
     */
    protected function resolveTargetRegion(string $oldKey, array $newKeys, array $regionMap): ?string
    {
        $mapped = $regionMap[$oldKey] ?? null;

        if (is_string($mapped) && $mapped !== '' && in_array($mapped, $newKeys, true)) {
            return $mapped;
        }

        if (in_array('main', $newKeys, true)) {
            return 'main';
        }

        return $newKeys[0] ?? null;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function reindexSortOrders(string $modelClass, string $groupKey): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, PageBlock|PageMasterBlock> $blocks */
        $blocks = $modelClass::query()->orderBy('sort_order')->get();
        $groups = $blocks->groupBy(fn ($block) => $block->{$groupKey}.'|'.$block->region_key);

        foreach ($groups as $groupBlocks) {
            foreach ($groupBlocks->values() as $index => $block) {
                if ((int) $block->sort_order !== $index) {
                    $block->sort_order = $index;
                    $block->save();
                }
            }
        }
    }
}
