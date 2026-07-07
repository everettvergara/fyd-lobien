<?php

namespace App\Modules\Content\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\Content\Models\ContentType;
use App\Modules\Content\Services\ContentUrlService;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class ContentTypeListingBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $typeKey = (string) ($config['content_type_key'] ?? '');

        if ($typeKey === '') {
            return ['listing' => null];
        }

        $type = ContentType::query()
            ->where('is_active', true)
            ->where('key', $typeKey)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->first();

        if ($type === null) {
            return ['listing' => null];
        }

        $queryParam = app(ContentUrlService::class)->paginationQueryParam($typeKey);
        $pageNumber = max(1, (int) request()->query($queryParam, 1));

        return [
            'listing' => PublicContent::contentTypeListing($type, $pageNumber, $queryParam),
        ];
    }
}
