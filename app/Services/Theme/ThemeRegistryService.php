<?php

namespace App\Services\Theme;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class ThemeRegistryService
{
    /** @var array<int, string> */
    protected const CORE_REQUIRED_PAGES = [
        'Page/Show.vue',
        'Search.vue',
    ];

    public function runtimePath(): string
    {
        return (string) config('fyd.themes.path', base_path('themes'));
    }

    public function contribPath(): string
    {
        return (string) config('fyd.themes.contrib_path', base_path('contrib_themes'));
    }

    public function defaultSlug(): string
    {
        return (string) config('fyd.themes.default', config('fyd.public.theme', 'fyd-default'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function installed(): Collection
    {
        return $this->scanDirectory($this->runtimePath());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function available(): Collection
    {
        $installedSlugs = $this->installed()->pluck('slug')->all();

        return $this->scanDirectory($this->contribPath())
            ->reject(fn (array $theme) => in_array($theme['slug'], $installedSlugs, true))
            ->values();
    }

    public function findInstalled(string $slug): ?array
    {
        return $this->installed()->firstWhere('slug', $slug);
    }

    public function findAvailable(string $slug): ?array
    {
        return $this->available()->firstWhere('slug', $slug)
            ?? $this->scanDirectory($this->contribPath())->firstWhere('slug', $slug);
    }

    /**
     * @return array<string, mixed>
     */
    public function manifestFromPath(string $directory): array
    {
        $manifestFile = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'theme.json';

        if (! is_file($manifestFile)) {
            throw new InvalidArgumentException("Theme manifest not found at [{$manifestFile}].");
        }

        $decoded = json_decode((string) file_get_contents($manifestFile), true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException("Invalid theme manifest JSON at [{$manifestFile}].");
        }

        $slug = basename($directory);

        if (! isset($decoded['slug'])) {
            $decoded['slug'] = $slug;
        }

        if ($decoded['slug'] !== $slug) {
            throw new InvalidArgumentException("Theme folder [{$slug}] does not match manifest slug [{$decoded['slug']}].");
        }

        $decoded['path'] = $directory;

        return $decoded;
    }

    public function fallbackSlug(): string
    {
        return $this->defaultSlug();
    }

    /**
     * @return array{valid: bool, errors: array<int, string>, warnings: array<int, string>}
     */
    public function validate(string $directory, ?array $manifest = null): array
    {
        $errors = [];
        $warnings = [];

        try {
            $manifest ??= $this->manifestFromPath($directory);
        } catch (InvalidArgumentException $exception) {
            return ['valid' => false, 'errors' => [$exception->getMessage()], 'warnings' => []];
        }

        foreach (['name', 'slug', 'version', 'assets'] as $key) {
            if (empty($manifest[$key])) {
                $errors[] = "Missing required manifest key [{$key}].";
            }
        }

        $scss = $manifest['assets']['scss'] ?? null;
        $js = $manifest['assets']['js'] ?? null;

        if (! is_string($scss) || ! is_file($directory.DIRECTORY_SEPARATOR.$scss)) {
            $errors[] = 'Theme SCSS entry file is missing or invalid.';
        }

        if (! is_string($js) || ! is_file($directory.DIRECTORY_SEPARATOR.$js)) {
            $errors[] = 'Theme JS entry file is missing or invalid.';
        }

        $regions = $manifest['regions'] ?? null;

        if (! is_array($regions) || $regions === []) {
            $errors[] = 'Theme manifest must define at least one region in [regions].';
        } else {
            $regionKeys = [];

            foreach ($regions as $index => $region) {
                if (! is_array($region) || empty($region['key'])) {
                    $errors[] = "Region at index [{$index}] is missing required [key].";
                } else {
                    $regionKeys[] = (string) $region['key'];
                }
            }

            $regionMap = $manifest['region_map'] ?? null;

            if (is_array($regionMap)) {
                foreach ($regionMap as $from => $to) {
                    if (! is_string($from) || $from === '' || ! is_string($to) || $to === '') {
                        $errors[] = 'region_map entries must use non-empty string keys and values.';

                        continue;
                    }

                    if (! in_array($to, $regionKeys, true)) {
                        $errors[] = "region_map target [{$to}] for [{$from}] is not a defined region.";
                    }
                }
            }
        }

        foreach (self::CORE_REQUIRED_PAGES as $page) {
            $pagePath = $directory.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.$page;

            if (! is_file($pagePath)) {
                $errors[] = "Required Inertia page [{$page}] is missing.";
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function scanDirectory(string $basePath): Collection
    {
        if (! is_dir($basePath)) {
            return collect();
        }

        $themes = collect();

        foreach (scandir($basePath) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $directory = $basePath.DIRECTORY_SEPARATOR.$entry;

            if (! is_dir($directory) || ! is_file($directory.DIRECTORY_SEPARATOR.'theme.json')) {
                continue;
            }

            try {
                $manifest = $this->manifestFromPath($directory);
                $validation = $this->validate($directory, $manifest);

                $themes->push(array_merge($manifest, [
                    'valid' => $validation['valid'],
                    'errors' => $validation['errors'],
                    'warnings' => $validation['warnings'],
                ]));
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $themes->sortBy('name')->values();
    }
}
