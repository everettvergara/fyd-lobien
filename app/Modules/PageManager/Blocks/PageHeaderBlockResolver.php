<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;

class PageHeaderBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        return [
            'title' => (string) ($config['title'] ?? $page->title),
            'summary' => (string) ($config['summary'] ?? $page->summary ?? ''),
            'showSummary' => (bool) ($config['show_summary'] ?? true),
        ];
    }
}
