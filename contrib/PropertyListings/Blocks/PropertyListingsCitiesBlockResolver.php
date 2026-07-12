<?php

namespace App\Modules\PropertyListings\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Modules\PropertyListings\Services\PropertyListingPublicService;

class PropertyListingsCitiesBlockResolver implements BlockResolver
{
    public const PER_PAGE = 36;

    public function __construct(
        protected PropertyListingPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $perPage = max(1, (int) ($config['per_page'] ?? self::PER_PAGE));

        $paginated = $this->publicService->paginateCollection(
            collect($this->publicService->citiesWithListings()),
            (int) request()->query('page', 1),
            $perPage,
        );

        return [
            'cities' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ];
    }
}
