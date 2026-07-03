<?php

namespace App\Modules\WebForms\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;

class WebformBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $slug = (string) ($config['webform_slug'] ?? '');

        if ($slug === '') {
            return ['slug' => ''];
        }

        return ['slug' => $slug];
    }
}
