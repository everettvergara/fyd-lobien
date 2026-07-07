<?php

namespace App\Modules\Newsletter\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;

class NewsletterBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $slug = (string) ($config['list_slug'] ?? '');

        if ($slug === '') {
            return ['slug' => ''];
        }

        return ['slug' => $slug];
    }
}
