<?php

namespace App\Modules\PropertyListings\Services;

use App\Enums\ContentStatus;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageBlock;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Support\ListingPathHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PropertyListingPageGenerationService
{
    public const BLOCK_DETAIL = 'property-listing-detail';

    public const BLOCK_CITY = 'property-listings-city';

    public const BLOCK_CITIES = 'property-listings-cities';

    public const BLOCK_SEARCH_BANNER = 'property-search-banner';

    public const BLOCK_SEARCH_RESULTS = 'property-search-results';

    public const BLOCK_PROPERTY_TYPES = 'property-listings-property-types';

    /**
     * Block types owned by the generator; used to detect generated pages.
     *
     * @var array<int, string>
     */
    public const MANAGED_BLOCK_TYPES = [
        self::BLOCK_DETAIL,
        self::BLOCK_CITY,
        self::BLOCK_CITIES,
        self::BLOCK_SEARCH_BANNER,
        self::BLOCK_SEARCH_RESULTS,
        self::BLOCK_PROPERTY_TYPES,
    ];

    public function __construct(
        protected PropertyListingPublicService $publicService,
        protected PropertyListingMenuService $menuService,
    ) {}

    /**
     * @param  callable(int $processed, int $total): void|null  $onProgress
     * @return array{created: int, updated: int, removed: int, errors: array<int, string>}
     */
    public function syncAll(?callable $onProgress = null): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'removed' => 0, 'errors' => []];
        $eligible = $this->publicService->eligibleListings();
        $citySlugs = $this->publicService->distinctCitySlugs($eligible);
        $total = count($citySlugs) + $eligible->count() + 3;
        $processed = 0;

        $expectedPaths = [];

        try {
            $expectedPaths[] = ListingPathHelper::indexPath();
            $result = $this->syncPropertiesIndexPage();
            $this->incrementStats($stats, $result);
        } catch (\Throwable $e) {
            $stats['errors'][] = "Properties page: {$e->getMessage()}";
        }

        $processed++;
        if ($onProgress !== null) {
            $onProgress($processed, $total);
        }

        try {
            $expectedPaths[] = ListingPathHelper::searchPath();
            $result = $this->syncSearchPage();
            $this->incrementStats($stats, $result);
        } catch (\Throwable $e) {
            $stats['errors'][] = "Search page: {$e->getMessage()}";
        }

        $processed++;
        if ($onProgress !== null) {
            $onProgress($processed, $total);
        }

        foreach ($citySlugs as $citySlug) {
            try {
                $path = ListingPathHelper::cityPath($citySlug);
                $expectedPaths[] = $path;
                $result = $this->syncCityPage($citySlug, $eligible);
                $this->incrementStats($stats, $result);
            } catch (\Throwable $e) {
                $stats['errors'][] = "City {$citySlug}: {$e->getMessage()}";
            }

            $processed++;
            if ($onProgress !== null) {
                $onProgress($processed, $total);
            }
        }

        foreach ($eligible as $listing) {
            try {
                $path = $listing->publicPath();
                if ($path !== null) {
                    $expectedPaths[] = $path;
                }
                $result = $this->syncListingPage($listing);
                $this->incrementStats($stats, $result);
            } catch (\Throwable $e) {
                $stats['errors'][] = "Listing {$listing->code}: {$e->getMessage()}";
            }

            $processed++;
            if ($onProgress !== null) {
                $onProgress($processed, $total);
            }
        }

        $removed = $this->cleanupOrphanPages($expectedPaths);
        $stats['removed'] += $removed;

        Listing::query()
            ->whereNotNull('public_page_path')
            ->get()
            ->each(function (Listing $listing) use (&$stats) {
                if (! $listing->isPublicPageEligible() && $this->removeListingPage($listing)) {
                    $stats['removed']++;
                }
            });

        try {
            $cities = collect($citySlugs)
                ->map(fn (string $slug) => [
                    'slug' => $slug,
                    'label' => $this->publicService->cityLabelForSlug($slug, $eligible),
                ])
                ->sortBy('label')
                ->values()
                ->all();

            $this->menuService->syncFooterMenu($cities);
        } catch (\Throwable $e) {
            $stats['errors'][] = "Footer menu: {$e->getMessage()}";
        }

        $processed++;
        if ($onProgress !== null) {
            $onProgress(min($processed, $total), $total);
        }

        return $stats;
    }

    /**
     * Generate the /properties hub page: search banner, city cards, and property type cards.
     *
     * @return array{created: int, updated: int}
     */
    public function syncPropertiesIndexPage(): array
    {
        $path = ListingPathHelper::indexPath();
        $created = Page::query()->where('path', $path)->doesntExist();

        $page = Page::updateOrCreate(
            ['path' => $path],
            [
                'slug' => Page::slugFromPath($path),
                'title' => 'Properties',
                'summary' => 'Browse property listings by city.',
                'body' => '',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        $page->saveSeo([
            'seo_title' => 'Properties',
            'meta_description' => 'Browse published property listings by city.',
            'sitemap_include' => true,
        ]);

        $this->syncPageBlocks($page, [
            ['type' => self::BLOCK_SEARCH_BANNER, 'sort' => 0, 'config' => ['banner_key' => 'default']],
            ['type' => self::BLOCK_CITIES, 'sort' => 1, 'config' => []],
            ['type' => self::BLOCK_PROPERTY_TYPES, 'sort' => 2, 'config' => ['title' => 'Browse by property type', 'per_page' => 9]],
        ]);

        return ['created' => $created ? 1 : 0, 'updated' => $created ? 0 : 1];
    }

    /**
     * Generate the /properties/search results page: search banner + results grid.
     *
     * @return array{created: int, updated: int}
     */
    public function syncSearchPage(): array
    {
        $path = ListingPathHelper::searchPath();
        $created = Page::query()->where('path', $path)->doesntExist();

        $page = Page::updateOrCreate(
            ['path' => $path],
            [
                'slug' => Page::slugFromPath($path),
                'title' => 'Search Properties',
                'summary' => 'Search published property listings.',
                'body' => '',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        $page->saveSeo([
            'seo_title' => 'Search Properties',
            'meta_description' => 'Search published property listings by city, property type, and availability.',
            'sitemap_include' => false,
        ]);

        $this->syncPageBlocks($page, [
            ['type' => self::BLOCK_SEARCH_RESULTS, 'sort' => 0, 'config' => []],
        ]);

        return ['created' => $created ? 1 : 0, 'updated' => $created ? 0 : 1];
    }

    /**
     * Remove every generated public page, block, and menu entry.
     *
     * @return array{pages_removed: int, menu_items_removed: int}
     */
    public function clearAll(): array
    {
        $pagesRemoved = 0;

        Page::query()
            ->where(function ($query) {
                $query->where('path', ListingPathHelper::indexPath())
                    ->orWhere('path', 'like', ListingPathHelper::PREFIX.'/%');
            })
            ->with('blocks')
            ->get()
            ->each(function (Page $page) use (&$pagesRemoved) {
                $hasManagedBlock = $page->blocks
                    ->contains(fn (PageBlock $block) => in_array($block->block_type, self::MANAGED_BLOCK_TYPES, true));

                if (! $hasManagedBlock) {
                    return;
                }

                DB::transaction(function () use ($page) {
                    $page->blocks()->delete();
                    $page->forceDelete();
                });

                $pagesRemoved++;
            });

        Listing::query()
            ->whereNotNull('public_page_path')
            ->update(['public_page_path' => null]);

        $menuItemsRemoved = $this->menuService->removeFooterMenu();

        return [
            'pages_removed' => $pagesRemoved,
            'menu_items_removed' => $menuItemsRemoved,
        ];
    }

    /**
     * @return array{created: int, updated: int}
     */
    public function syncListingPage(Listing $listing): array
    {
        if (! $listing->isPublicPageEligible()) {
            $this->removeListingPage($listing);

            return ['created' => 0, 'updated' => 0];
        }

        $path = $listing->publicPath();
        if ($path === null) {
            return ['created' => 0, 'updated' => 0];
        }

        $existing = Page::query()->where('path', $path)->first();
        $created = $existing === null;

        if ($listing->public_page_path && $listing->public_page_path !== $path) {
            $this->deletePageAtPath($listing->public_page_path);
        }

        $page = Page::updateOrCreate(
            ['path' => $path],
            [
                'slug' => Page::slugFromPath($path),
                'title' => $listing->name,
                'summary' => $this->listingPageSummary($listing),
                'body' => '',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        $page->saveSeo([
            'seo_title' => $listing->name,
            'meta_description' => $this->listingMetaDescription($listing),
            'sitemap_include' => true,
        ]);

        $this->syncPageBlocks($page, [
            ['type' => self::BLOCK_DETAIL, 'sort' => 0, 'config' => []],
            ['type' => self::BLOCK_CITY, 'sort' => 1, 'config' => []],
        ]);

        $listing->update(['public_page_path' => $path]);

        return ['created' => $created ? 1 : 0, 'updated' => $created ? 0 : 1];
    }

    /**
     * @return array{created: int, updated: int}
     */
    public function syncCityPage(string $citySlug, ?Collection $eligibleListings = null): array
    {
        $eligible = $eligibleListings ?? $this->publicService->eligibleListings();
        $cityListings = $eligible->filter(fn (Listing $listing) => $listing->citySlug() === $citySlug);

        if ($cityListings->isEmpty()) {
            $this->deletePageAtPath(ListingPathHelper::cityPath($citySlug));

            return ['created' => 0, 'updated' => 0];
        }

        $path = ListingPathHelper::cityPath($citySlug);
        $existing = Page::query()->where('path', $path)->first();
        $created = $existing === null;
        $cityLabel = $this->publicService->cityLabelForSlug($citySlug, $eligible);

        $page = Page::updateOrCreate(
            ['path' => $path],
            [
                'slug' => Page::slugFromPath($path),
                'title' => $cityLabel.' Properties',
                'summary' => 'Browse published property listings in '.$cityLabel.'.',
                'body' => '',
                'status' => ContentStatus::Published,
                'published_at' => now(),
            ],
        );

        $page->saveSeo([
            'seo_title' => $cityLabel.' Properties',
            'meta_description' => 'Explore property listings in '.$cityLabel.'.',
            'sitemap_include' => true,
        ]);

        $this->syncPageBlocks($page, [
            ['type' => self::BLOCK_CITY, 'sort' => 0, 'config' => []],
        ]);

        return ['created' => $created ? 1 : 0, 'updated' => $created ? 0 : 1];
    }

    public function removeListingPage(Listing $listing): bool
    {
        $path = $listing->public_page_path ?: $listing->publicPath();
        $removed = false;

        if ($path !== null) {
            $removed = $this->deletePageAtPath($path);
        }

        if ($listing->public_page_path !== null) {
            $listing->update(['public_page_path' => null]);
        }

        return $removed;
    }

    /**
     * @param  array<int, array{type: string, sort: int, config?: array<string, mixed>}>  $blocks
     */
    protected function syncPageBlocks(Page $page, array $blocks): void
    {
        $types = collect($blocks)->pluck('type')->all();

        $page->blocks()
            ->whereIn('block_type', self::MANAGED_BLOCK_TYPES)
            ->whereNotIn('block_type', $types)
            ->delete();

        foreach ($blocks as $block) {
            PageBlock::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'region_key' => 'main',
                    'block_type' => $block['type'],
                ],
                [
                    'sort_order' => $block['sort'],
                    'config' => $block['config'] ?? [],
                ],
            );
        }
    }

    /**
     * @param  array<int, string>  $expectedPaths
     */
    protected function cleanupOrphanPages(array $expectedPaths): int
    {
        $expected = collect($expectedPaths)->unique()->flip();
        $removed = 0;

        Page::query()
            ->where('path', 'like', ListingPathHelper::PREFIX.'/%')
            ->with('blocks')
            ->get()
            ->each(function (Page $page) use ($expected, &$removed) {
                if ($expected->has($page->path)) {
                    return;
                }

                $hasManagedBlock = $page->blocks
                    ->contains(fn (PageBlock $block) => in_array($block->block_type, self::MANAGED_BLOCK_TYPES, true));

                if ($hasManagedBlock && $this->deletePageAtPath($page->path)) {
                    $removed++;
                }
            });

        Listing::query()
            ->whereNotNull('public_page_path')
            ->whereNotIn('public_page_path', $expectedPaths)
            ->update(['public_page_path' => null]);

        return $removed;
    }

    protected function deletePageAtPath(string $path): bool
    {
        $page = Page::query()->where('path', $path)->first();
        if ($page === null) {
            return false;
        }

        DB::transaction(function () use ($page) {
            $page->blocks()->delete();
            $page->delete();
        });

        return true;
    }

    /**
     * @param  array{created: int, updated: int, removed: int, errors: array<int, string>}  $stats
     * @param  array{created: int, updated: int}  $result
     */
    protected function incrementStats(array &$stats, array $result): void
    {
        $stats['created'] += $result['created'];
        $stats['updated'] += $result['updated'];
    }

    public function countExistingPropertyPages(): int
    {
        return Page::query()
            ->where(function ($query) {
                $query->where('path', ListingPathHelper::indexPath())
                    ->orWhere('path', 'like', ListingPathHelper::PREFIX.'/%');
            })
            ->count();
    }

    protected function listingPageSummary(Listing $listing): string
    {
        if (filled($listing->summary)) {
            return trim((string) $listing->summary);
        }

        return trim(($listing->city ?? '').($listing->address ? ' — '.$listing->address : ''));
    }

    protected function listingMetaDescription(Listing $listing): string
    {
        if (filled($listing->summary)) {
            return trim((string) $listing->summary);
        }

        if (filled($listing->description)) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $listing->description)) ?? '');

            if ($plain !== '') {
                return mb_strlen($plain) > 160 ? mb_substr($plain, 0, 157).'...' : $plain;
            }
        }

        return sprintf(
            '%s property listing in %s.',
            $listing->name,
            $listing->city ?? 'the Philippines',
        );
    }
}
