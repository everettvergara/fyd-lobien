<?php

namespace App\Modules\Careers\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\Careers\Services\CareerPublicService;
use App\Modules\PageManager\Models\Page;

class CareersListingBlockResolver implements BlockResolver
{
    public const PER_PAGE = 9;

    public function __construct(
        protected CareerPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $perPage = max(1, (int) ($config['per_page'] ?? self::PER_PAGE));

        $allJobs = $this->publicService->listOpenJobs()
            ->map(fn ($job) => $this->publicService->toPublicDto($job));

        $paginated = $this->publicService->paginateCollection(
            $allJobs,
            (int) request()->query('page', 1),
            $perPage,
        );

        return [
            'jobs' => $paginated['items'],
            'pagination' => $paginated['pagination'],
        ];
    }
}
