<?php

namespace App\Modules\Newsletter\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\Newsletter\Services\NewsletterPublicService;
use App\Modules\PageManager\Models\Page;

class NewsletterBlockResolver implements BlockResolver
{
    public function __construct(
        protected NewsletterPublicService $publicService,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        return $this->publicService->blockProps((string) ($config['list_slug'] ?? ''));
    }
}
