<?php

namespace App\Services;

use App\Enums\MenuLocation;
use App\Models\Setting;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;

class NavigationService
{
    public function header(): array
    {
        return $this->forLocation(MenuLocation::Header);
    }

    public function footer(): array
    {
        return $this->forLocation(MenuLocation::Footer);
    }

    protected function forLocation(MenuLocation $location): array
    {
        $menu = Menu::where('location', $location)->with(['allItems.children.children'])->first();

        if (! $menu) {
            return $this->defaultNavigation($location);
        }

        $topLevel = $menu->allItems->whereNull('parent_id')->sortBy('sort_order');

        return $topLevel->map(fn (MenuItem $item) => $this->formatItem($item))->values()->all();
    }

    protected function formatItem(MenuItem $item): array
    {
        return [
            'title' => $item->title,
            'url' => $this->resolveUrl($item),
            'target' => $item->target,
            'children' => $item->children->map(fn (MenuItem $child) => $this->formatItem($child))->values()->all(),
        ];
    }

    protected function resolveUrl(MenuItem $item): string
    {
        if ($item->link_type?->value === 'external') {
            return $item->url ?? '#';
        }

        $url = $item->url ?? '/';

        return str_starts_with($url, '/') ? $url : '/'.$url;
    }

    protected function defaultNavigation(MenuLocation $location): array
    {
        if ($location === MenuLocation::Footer) {
            return [
                ['title' => 'Home', 'url' => '/', 'target' => '_self', 'children' => []],
                ['title' => 'Blog', 'url' => '/blog', 'target' => '_self', 'children' => []],
            ];
        }

        return [
            ['title' => 'Home', 'url' => '/', 'target' => '_self', 'children' => []],
            ['title' => 'Blog', 'url' => '/blog', 'target' => '_self', 'children' => []],
            ['title' => 'Contact', 'url' => '/contact', 'target' => '_self', 'children' => []],
        ];
    }

    public function siteInfo(): array
    {
        return [
            'name' => Setting::get('general', 'website_name', config('fyd.name')),
            'tagline' => Setting::get('general', 'tagline', ''),
            'contact' => [
                'email' => Setting::get('contact', 'email', ''),
                'phone' => Setting::get('contact', 'phone', ''),
                'address' => Setting::get('contact', 'address', ''),
            ],
            'social' => [
                'facebook' => Setting::get('social', 'facebook', ''),
                'twitter' => Setting::get('social', 'twitter', ''),
                'instagram' => Setting::get('social', 'instagram', ''),
                'linkedin' => Setting::get('social', 'linkedin', ''),
            ],
        ];
    }
}
