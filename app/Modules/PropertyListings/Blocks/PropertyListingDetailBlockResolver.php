<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Modules\PropertyListings\Support\ListingPathHelper;

class PropertyListingDetailBlockResolver implements BlockResolver
{
    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $parsed = ListingPathHelper::parsePagePath($page->path);
        if ($parsed === null || ! isset($parsed['listing_slug'])) {
            return ['listing' => null];
        }

        $listing = $this->publicService->findPublishedByCityAndSlug(
            $parsed['city_slug'],
            $parsed['listing_slug'],
        );

        if ($listing === null) {
            return ['listing' => null];
        }

        return [
            'listing' => $this->publicService->toDetailDto($listing),
        ];
    }
}
