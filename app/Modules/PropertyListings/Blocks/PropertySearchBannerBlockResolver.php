<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Models\PropertySearchBanner;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Modules\PropertyListings\Support\ListingPathHelper;

class PropertySearchBannerBlockResolver implements BlockResolver
{
    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $key = (string) ($config['banner_key'] ?? '');
        $banner = $key !== ''
            ? PropertySearchBanner::query()
                ->where('key', $key)
                ->where('is_active', true)
                ->with('backgroundImage')
                ->first()
            : null;

        $background = $banner?->backgroundImage;

        return [
            'heading' => $banner?->heading ?? 'Find your property',
            'background_image_url' => $background?->url(),
            'background_image_alt' => $background?->alt_text ?? '',
            'action_url' => url(ListingPathHelper::searchPath()),
            'cities' => $this->publicService->cityOptions(),
            'property_types' => $this->publicService->propertyTypeOptions(),
            'filters' => $this->publicService->normalizeSearchFilters(request()->query()),
        ];
    }
}
