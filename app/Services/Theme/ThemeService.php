<?php

namespace App\Services\Theme;

use App\Modules\Cache\Services\PublicCacheService;
use App\Services\ActivityLogger;
use App\Services\SettingsService;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class ThemeService
{
    public function __construct(
        protected ThemeRegistryService $registry,
        protected SettingsService $settings,
        protected PublicCacheService $publicCache,
    ) {}

    public function defaultSlug(): string
    {
        return $this->registry->defaultSlug();
    }

    public function activeSlug(): string
    {
        $slug = (string) $this->settings->get('appearance', 'active_theme', $this->defaultSlug());

        if ($this->isValidInstalled($slug)) {
            return $slug;
        }

        return $this->defaultSlug();
    }

    /**
     * @return array<string, mixed>
     */
    public function activeManifest(): array
    {
        $slug = $this->activeSlug();
        $manifest = $this->registry->findInstalled($slug);

        if ($manifest !== null) {
            return $manifest;
        }

        $fallback = $this->registry->findInstalled($this->defaultSlug());

        if ($fallback === null) {
            throw new RuntimeException('Default public theme is not installed.');
        }

        return $fallback;
    }

    /**
     * @return array<int, array{key: string, label: string, description?: string}>
     */
    public function activeRegions(): array
    {
        return $this->regionsForSlug($this->activeSlug());
    }

    /**
     * @return array<int, array{key: string, label: string, description?: string}>
     */
    public function regionsForSlug(string $slug): array
    {
        $manifest = $this->registry->findInstalled($slug);
        $regions = is_array($manifest) ? ($manifest['regions'] ?? null) : null;

        if (is_array($regions) && $regions !== []) {
            return array_values(array_filter($regions, fn ($region) => is_array($region) && ! empty($region['key'])));
        }

        return $this->defaultRegions();
    }

    /**
     * @return array<int, string>
     */
    public function regionKeysForSlug(string $slug): array
    {
        return array_values(array_map(
            fn (array $region) => (string) $region['key'],
            $this->regionsForSlug($slug),
        ));
    }

    /**
     * @return array<string, string>
     */
    public function regionMapForSlug(string $slug): array
    {
        $manifest = $this->registry->findInstalled($slug);

        if (! is_array($manifest)) {
            return [];
        }

        $map = $manifest['region_map'] ?? [];

        if (! is_array($map)) {
            return [];
        }

        $normalized = [];

        foreach ($map as $from => $to) {
            if (is_string($from) && is_string($to) && $from !== '' && $to !== '') {
                $normalized[$from] = $to;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, array{key: string, label: string, description?: string}>
     */
    public function defaultRegions(): array
    {
        return [
            ['key' => 'hero', 'label' => 'Hero', 'description' => 'Full-width top banner area'],
            ['key' => 'main', 'label' => 'Main', 'description' => 'Primary content column'],
            ['key' => 'sidebar', 'label' => 'Sidebar', 'description' => 'Optional side column'],
            ['key' => 'footer', 'label' => 'Footer', 'description' => 'Above site footer'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function viteAssets(): array
    {
        $manifest = $this->activeManifest();
        $base = rtrim((string) ($manifest['path'] ?? ''), DIRECTORY_SEPARATOR);
        $slug = $manifest['slug'];

        $scss = $manifest['assets']['scss'] ?? 'scss/theme.scss';
        $js = $manifest['assets']['js'] ?? 'assets/app.js';

        return [
            "themes/{$slug}/{$scss}",
            "themes/{$slug}/{$js}",
        ];
    }

    /**
     * @return array{preserved: int, remapped: int, details: array<int, string>}|null
     */
    public function setActive(string $slug, ?int $userId = null): ?array
    {
        if (! $this->isValidInstalled($slug)) {
            throw new InvalidArgumentException("Theme [{$slug}] is not installed or invalid.");
        }

        $from = $this->activeSlug();
        $summary = null;

        if ($from !== $slug) {
            $summary = app(ThemeBlockMigrationService::class)->migrate($from, $slug);
        }

        $this->settings->set('appearance', 'active_theme', $slug, 'string');
        $this->publicCache->clearAll();

        ActivityLogger::log('themes', 'activated', null, [
            'theme' => $slug,
            'user_id' => $userId,
            'block_migration' => $summary,
        ]);

        return $summary;
    }

    /**
     * @return array{warnings: array<int, string>}
     */
    public function installFromContrib(string $slug, ?int $userId = null, bool $overwrite = false): array
    {
        $available = $this->registry->findAvailable($slug);

        if ($available === null) {
            throw new InvalidArgumentException("Theme [{$slug}] was not found under contrib_themes.");
        }

        $source = (string) ($available['path'] ?? $this->registry->contribPath().DIRECTORY_SEPARATOR.$slug);
        $target = $this->registry->runtimePath().DIRECTORY_SEPARATOR.$slug;

        $validation = $this->registry->validate($source);

        if (! $validation['valid']) {
            throw new InvalidArgumentException('Theme validation failed: '.implode(' ', $validation['errors']));
        }

        if (is_dir($target)) {
            if (($available['protected'] ?? false) && $slug === $this->defaultSlug() && ! $overwrite) {
                throw new InvalidArgumentException('The default theme is already installed.');
            }

            if (! $overwrite) {
                throw new InvalidArgumentException("Theme [{$slug}] is already installed.");
            }

            File::deleteDirectory($target);
        }

        File::copyDirectory($source, $target);

        ActivityLogger::log('themes', 'installed', null, [
            'theme' => $slug,
            'user_id' => $userId,
            'warnings' => $validation['warnings'],
        ]);

        return ['warnings' => $validation['warnings']];
    }

    public function reinstallDefault(?int $userId = null): void
    {
        $this->installFromContrib($this->defaultSlug(), $userId, true);
    }

    protected function isValidInstalled(string $slug): bool
    {
        $theme = $this->registry->findInstalled($slug);

        return $theme !== null && ($theme['valid'] ?? false);
    }
}
