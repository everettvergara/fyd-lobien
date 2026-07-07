<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Modules\PropertyListings\Support\ListingPathHelper;

class PropertyListingsCityBlockResolver implements BlockResolver
{
    public const PER_PAGE = 9;

    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $parsed = ListingPathHelper::parsePagePath($page->path);
        if ($parsed === null) {
            return ['mode' => 'related', 'city_slug' => '', 'city_label' => '', 'listings' => []];
        }

        $citySlug = $parsed['city_slug'];

        if (isset($parsed['listing_slug'])) {
            return $this->relatedListings($citySlug, $parsed['listing_slug']);
        }

        return $this->cityListings($citySlug, $config);
    }

    /**
     * "More in {city}" grid shown on listing detail pages.
     *
     * @return array<string, mixed>
     */
    protected function relatedListings(string $citySlug, string $listingSlug): array
    {
        $current = $this->publicService->findPublishedByCityAndSlug($citySlug, $listingSlug);

        $listings = $this->publicService
            ->randomPublishedForCity($citySlug, 5, $current?->id)
            ->map(fn (Listing $listing) => $this->publicService->toListItemDto($listing))
            ->values()
            ->all();

        return [
            'mode' => 'related',
            'city_slug' => $citySlug,
            'city_label' => $this->publicService->cityLabelForSlug($citySlug),
            'listings' => $listings,
        ];
    }

    /**
     * Full city page: city profile (image, summary, description), search filters
     * defaulting to "all", and the filtered listing card grid.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function cityListings(string $citySlug, array $config): array
    {
        $filters = $this->publicService->normalizeSearchFilters(request()->query());
        $filters['city'] = $citySlug;
        $perPage = max(1, (int) ($config['per_page'] ?? self::PER_PAGE));

        $results = $this->publicService->searchPublished($filters);
        $paginated = $this->publicService->paginateCollection(
            $results->map(fn (Listing $listing) => $this->publicService->toListItemDto($listing)),
            (int) request()->query('page', 1),
            $perPage,
        );

        return [
            'mode' => 'city',
            'city_slug' => $citySlug,
            'city_label' => $this->publicService->cityLabelForSlug($citySlug),
            'city' => $this->publicService->cityDto($citySlug),
            'filters' => $filters,
            'action_url' => url(ListingPathHelper::cityPath($citySlug)),
            'property_types' => $this->publicService->propertyTypeOptions(),
            'listings' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ];
    }
}
