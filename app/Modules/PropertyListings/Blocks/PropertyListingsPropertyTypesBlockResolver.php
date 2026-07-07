<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;

class PropertyListingsPropertyTypesBlockResolver implements BlockResolver
{
    public const PER_PAGE = 9;

    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $perPage = max(1, (int) ($config['per_page'] ?? self::PER_PAGE));

        $paginated = $this->publicService->paginateCollection(
            collect($this->publicService->propertyTypeCards()),
            (int) request()->query('page', 1),
            $perPage,
        );

        return [
            'property_types' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ];
    }
}
