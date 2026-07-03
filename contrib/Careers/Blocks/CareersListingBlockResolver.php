<?php

namespace App\Modules\Careers\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;

class CareersListingBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        return [];
    }
}
