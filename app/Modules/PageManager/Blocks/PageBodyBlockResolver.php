<?php

namespace App\Modules\PageManager\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Support\HtmlSanitizer;

class PageBodyBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        return [
            'body' => HtmlSanitizer::clean($page->body ?? ''),
        ];
    }
}
