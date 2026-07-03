<?php

namespace App\Modules\Banners\Blocks;

use App\Contracts\BlockResolver;
use App\Modules\PageManager\Models\Page;
use App\Support\PublicContent;

class BannerBlockResolver implements BlockResolver
{
    public function resolve(array $config, Page $page): array
    {
        $key = (string) ($config['banner_key'] ?? '');

        if ($key === '') {
            return ['banner' => null];
        }

        return [
            'banner' => PublicContent::bannerByKey($key),
        ];
    }
}
