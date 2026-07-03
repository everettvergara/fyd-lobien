<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class FeaturedContentBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $limit = (int) ($config['limit'] ?? 3);
        $contentType = (string) ($config['content_type'] ?? 'page');

        $items = \App\Modules\Content\Models\Content::published()
            ->where('content_type', $contentType)
            ->with('featuredImage')
            ->latest('published_at')
            ->limit(max(1, min($limit, 12)))
            ->get()
            ->map(fn ($content) => PublicContent::contentCard($content))
            ->values()
            ->all();

        return [
            'heading' => (string) ($config['heading'] ?? 'Featured Content'),
            'items' => $items,
        ];
    }
}
