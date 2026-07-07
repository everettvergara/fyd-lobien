<?php

namespace App\Modules\PropertyListings\Services;

use App\Enums\MenuLocation;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;
use App\Modules\PropertyListings\Support\ListingPathHelper;

class PropertyListingMenuService
{
    public const PARENT_TITLE = 'Properties';

    /**
     * Create or refresh the footer "Properties" menu entry: a top-level link to
     * /properties with one child link per generated city page. Stale city links
     * are removed; other footer items are left untouched.
     *
     * @param  array<int, array{slug: string, label: string}>  $cities
     * @return array{items: int}
     */
    public function syncFooterMenu(array $cities): array
    {
        $footer = Menu::updateOrCreate(
            ['location' => MenuLocation::Footer],
            ['name' => 'Footer Navigation'],
        );

        $parent = MenuItem::updateOrCreate(
            [
                'menu_id' => $footer->id,
                'parent_id' => null,
                'url' => ListingPathHelper::indexPath(),
            ],
            [
                'title' => self::PARENT_TITLE,
                'link_type' => 'internal',
                'target' => '_self',
            ],
        );

        $expectedUrls = [];
        $count = 1;

        foreach (array_values($cities) as $index => $city) {
            $url = ListingPathHelper::cityPath($city['slug']);
            $expectedUrls[] = $url;

            MenuItem::updateOrCreate(
                [
                    'menu_id' => $footer->id,
                    'parent_id' => $parent->id,
                    'url' => $url,
                ],
                [
                    'title' => $city['label'],
                    'link_type' => 'internal',
                    'target' => '_self',
                    'sort_order' => $index,
                ],
            );

            $count++;
        }

        MenuItem::query()
            ->where('menu_id', $footer->id)
            ->where('parent_id', $parent->id)
            ->whereNotIn('url', $expectedUrls)
            ->delete();

        return ['items' => $count];
    }

    /**
     * Remove the managed "Properties" footer entry and its children.
     */
    public function removeFooterMenu(): int
    {
        $footer = Menu::query()->where('location', MenuLocation::Footer)->first();

        if ($footer === null) {
            return 0;
        }

        $parents = MenuItem::query()
            ->where('menu_id', $footer->id)
            ->whereNull('parent_id')
            ->where('url', ListingPathHelper::indexPath())
            ->get();

        $removed = 0;

        foreach ($parents as $parent) {
            $removed += MenuItem::query()
                ->where('menu_id', $footer->id)
                ->where('parent_id', $parent->id)
                ->delete();

            $parent->delete();
            $removed++;
        }

        return $removed;
    }
}
