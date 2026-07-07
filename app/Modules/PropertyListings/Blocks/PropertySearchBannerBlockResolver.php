<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Models\Media;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;
use App\Modules\PropertyListings\Support\ListingPathHelper;

class PropertySearchBannerBlockResolver implements BlockResolver
{
    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $backgroundImageId = (int) ($config['background_image_id'] ?? 0);
        $background = $backgroundImageId > 0 ? Media::query()->find($backgroundImageId) : null;

        return [
            'heading' => (string) ($config['heading'] ?? 'Find your property'),
            'background_image_url' => $background?->url(),
            'background_image_alt' => $background?->alt_text ?? '',
            'action_url' => url(ListingPathHelper::searchPath()),
            'cities' => $this->publicService->cityOptions(),
            'property_types' => $this->publicService->propertyTypeOptions(),
            'filters' => $this->publicService->normalizeSearchFilters(request()->query()),
        ];
    }
}
