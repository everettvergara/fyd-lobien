<?php

namespace App\Modules\Menus\Services;

use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;

class MenuService
{
    public function create(array $attributes, array $items): Menu
    {
        $menu = Menu::create($attributes);
        $this->syncItems($menu, $items);

        return $menu;
    }

    public function update(Menu $menu, array $attributes, array $items): Menu
    {
        $menu->update($attributes);
        $this->syncItems($menu, $items);

        return $menu;
    }

    public function syncItems(Menu $menu, array $items): void
    {
        $menu->allItems()->delete();

        foreach ($items as $index => $item) {
            if (empty($item['title'])) {
                continue;
            }

            MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'link_type' => $item['link_type'] ?? 'internal',
                'target' => $item['target'] ?? '_self',
                'sort_order' => $index,
            ]);
        }
    }
}
