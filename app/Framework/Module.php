<?php

namespace App\Framework;

abstract class Module
{
    abstract public function name(): string;

    /**
     * @return array<class-string, class-string>
     */
    public function policies(): array
    {
        return [];
    }

    /**
     * @return array<int, MenuItem>
     */
    public function menuItems(): array
    {
        return [];
    }

    protected function menuItem(
        string $label,
        string $routeName,
        string $permission,
        string $icon,
        ?string $section = null,
        ?string $routePattern = null,
        int $sort = 0,
    ): MenuItem {
        return new MenuItem($label, $routeName, $permission, $icon, $section, $routePattern, $sort);
    }
}
