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

    /**
     * @return array<int, array{module: string, action: string, display_name: string}>
     */
    public function permissions(): array
    {
        return [];
    }

    /**
     * @return array{module: string, action: string, display_name: string}
     */
    protected function permissionEntry(string $module, string $action, string $displayName): array
    {
        return ['module' => $module, 'action' => $action, 'display_name' => $displayName];
    }

    protected function menuItem(
        string $label,
        string $routeName,
        string $permission,
        string $icon,
        ?string $section = null,
        ?string $routePattern = null,
        int $sort = 0,
        array $query = [],
    ): MenuItem {
        return new MenuItem($label, $routeName, $permission, $icon, $section, $routePattern, $sort, $query);
    }
}
