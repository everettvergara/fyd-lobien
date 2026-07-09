<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\Content\Services\ContentPageSyncService;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class PageHeaderBlockResolver implements BlockResolver
{
    public function __construct(
        protected ContentPageSyncService $contentPages,
    ) {}

    public function resolve(array $config, Page $page): array
    {
        $props = [
            'title' => (string) ($config['title'] ?? $page->title),
            'summary' => (string) ($config['summary'] ?? $page->summary ?? ''),
            'showSummary' => (bool) ($config['show_summary'] ?? true),
            'featuredImage' => null,
            'author' => null,
            'publishedAt' => null,
            'contentType' => null,
        ];

        $content = $this->contentPages->contentForPage($page);

        if ($content === null) {
            return $props;
        }

        $entry = PublicContent::entry($content);

        $props['featuredImage'] = $entry['featuredImage'];
        $props['author'] = $entry['author'];
        $props['publishedAt'] = $entry['publishedAt'];
        $props['contentType'] = $entry['contentType'];

        return $props;
    }
}
