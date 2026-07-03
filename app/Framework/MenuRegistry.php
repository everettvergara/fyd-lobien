<?php

namespace App\Framework;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class MenuRegistry
{
    /** @var array<int, MenuItem> */
    protected array $items = [];

    public function register(MenuItem $item): void
    {
        $this->items[] = $item;
    }

    public function reset(): void
    {
        $this->items = [];
    }

    /**
     * @return array{core: array<int, array{title: ?string, items: array<int, array<string, mixed>>}>, business: array<int, array{title: ?string, items: array<int, array<string, mixed>>}>}
     */
    public function panelsFor(?User $user): array
    {
        return [
            'core' => $this->sectionsForPanel($user, MenuItem::PANEL_CORE),
            'business' => $this->sectionsForPanel($user, MenuItem::PANEL_BUSINESS),
        ];
    }

    /**
     * @return array<int, array{title: ?string, items: array<int, array{label: string, url: string, icon: string, active: bool}>}>
     */
    public function sectionsFor(?User $user): array
    {
        return $this->panelsFor($user)['core'];
    }

    /**
     * @return array<int, array{title: ?string, items: array<int, array{label: string, url: string, icon: string, active: bool}>}>
     */
    protected function sectionsForPanel(?User $user, string $panel): array
    {
        if (! $user) {
            return [];
        }

        $visible = collect($this->items)
            ->filter(fn (MenuItem $item) => $item->panel === $panel && $user->hasPermission($item->permission))
            ->sortBy('sort')
            ->values();

        $sections = [];
        $order = [];

        foreach ($visible as $item) {
            $key = $item->section ?? '';
            if (! array_key_exists($key, $sections)) {
                $sections[$key] = [
                    'title' => $item->section,
                    'items' => [],
                ];
                $order[] = $key;
            }

            $url = Route::has($item->routeName) ? route($item->routeName, $item->query) : '#';

            $sections[$key]['items'][] = [
                'label' => $item->label,
                'url' => $url,
                'icon' => $item->icon,
                'active' => $item->isActive(),
            ];
        }

        return array_values(array_filter(
            array_map(fn (string $key) => $sections[$key], $order),
            fn (array $section) => $section['items'] !== [],
        ));
    }
}
