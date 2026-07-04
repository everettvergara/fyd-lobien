<?php

namespace App\Modules\ContentBlocks\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\ContentBlocks\Services\ContentBlockRenderingService;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class ContentBlockBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $key = (string) ($config['content_block_key'] ?? '');

        if ($key === '') {
            return ['contentBlock' => null];
        }

        $queryParam = app(ContentBlockRenderingService::class)->paginationQueryParam($key);
        $pageNumber = max(1, (int) request()->query($queryParam, 1));

        return [
            'contentBlock' => PublicContent::contentBlockByKey($key, $pageNumber),
        ];
    }
}
