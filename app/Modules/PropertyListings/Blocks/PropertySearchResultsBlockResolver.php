<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Modules\PropertyListings\Support\ListingPathHelper;

class PropertySearchResultsBlockResolver implements BlockResolver
{
    public const PER_PAGE = 36;

    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $filters = $this->publicService->normalizeSearchFilters(request()->query());
        $perPage = max(1, (int) ($config['per_page'] ?? self::PER_PAGE));

        $results = $this->publicService->searchPublished($filters);
        $paginated = $this->publicService->paginateCollection(
            $results->map(fn (Listing $listing) => $this->publicService->toListItemDto($listing)),
            (int) request()->query('page', 1),
            $perPage,
        );

        return [
            'filters' => $filters,
            'action_url' => url(ListingPathHelper::searchPath()),
            'cities' => $this->publicService->cityOptions(),
            'property_types' => $this->publicService->propertyTypeOptions(),
            'listings' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ];
    }
}
