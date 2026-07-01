<?php

namespace App\Services;

use App\Enums\MenuLocation;
use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Models\MenuItem;

class NavigationService
{
    public function __construct(
        protected SettingsService $settings,
    ) {}

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
            'name' => $this->settings->get('general', 'website_name', config('fyd.name')),
            'tagline' => $this->settings->get('general', 'tagline', ''),
            'contact' => [
                'email' => $this->settings->get('contact', 'email', ''),
                'phone' => $this->settings->get('contact', 'phone', ''),
                'address' => $this->settings->get('contact', 'address', ''),
            ],
            'social' => [
                'facebook' => $this->settings->get('social', 'facebook', ''),
                'twitter' => $this->settings->get('social', 'twitter', ''),
                'instagram' => $this->settings->get('social', 'instagram', ''),
                'linkedin' => $this->settings->get('social', 'linkedin', ''),
            ],
        ];
    }
}
