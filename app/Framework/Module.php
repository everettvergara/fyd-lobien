<?php

namespace App\Framework;

abstract class Module
{
    /** @var array<string, array<string, mixed>> */
    protected static array $manifestCache = [];

    abstract public function name(): string;

    public function isInstallable(): bool
    {
        return false;
    }

    public function description(): string
    {
        return (string) ($this->manifest()['description'] ?? '');
    }

    public function version(): string
    {
        return (string) ($this->manifest()['version'] ?? '1.0.0');
    }

    public function group(): string
    {
        return (string) ($this->manifest()['group'] ?? $this->name());
    }

    public function groupIcon(): string
    {
        return (string) ($this->manifest()['group_icon'] ?? 'bi-box');
    }

    public function groupSort(): int
    {
        return (int) ($this->manifest()['group_sort'] ?? 0);
    }

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
     * @return array<int, class-string<\Illuminate\Database\Seeder>>
     */
    public function seeders(): array
    {
        return [];
    }

    /**
     * @return array<int, class-string>
     */
    public function commands(): array
    {
        return [];
    }

    public function uninstall(): void {}

    /**
     * @return array<int, PublicBlock>
     */
    public function publicBlocks(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        $class = static::class;

        if (! array_key_exists($class, self::$manifestCache)) {
            $path = $this->manifestPath();

            if (! is_file($path)) {
                self::$manifestCache[$class] = [];

                return self::$manifestCache[$class];
            }

            $decoded = json_decode((string) file_get_contents($path), true);

            self::$manifestCache[$class] = is_array($decoded) ? $decoded : [];
        }

        return self::$manifestCache[$class];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function features(): array
    {
        $features = $this->manifest()['features'] ?? [];

        return is_array($features) ? $features : [];
    }

    protected function manifestPath(): string
    {
        return dirname((new \ReflectionClass($this))->getFileName()).'/module.json';
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
        string $panel = MenuItem::PANEL_CORE,
    ): MenuItem {
        if ($this->isInstallable()) {
            $section = $this->group();
            $panel = MenuItem::PANEL_BUSINESS;
        }

        return new MenuItem($label, $routeName, $permission, $icon, $section, $routePattern, $sort, $query, $panel);
    }
}
