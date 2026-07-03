<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class LatestArticlesBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $limit = (int) ($config['limit'] ?? 3);

        $items = \App\Modules\Content\Models\Content::published()
            ->where('content_type', 'article')
            ->with('featuredImage')
            ->latest('published_at')
            ->limit(max(1, min($limit, 12)))
            ->get()
            ->map(fn ($content) => PublicContent::contentCard($content))
            ->values()
            ->all();

        return [
            'heading' => (string) ($config['heading'] ?? 'Latest Articles'),
            'items' => $items,
        ];
    }
}
