<?php

namespace App\Modules\Banners\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Modules\Banners\Models\Banner;

class BannerKeyOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return Banner::query()
            ->orderBy('name')
            ->get(['key', 'name'])
            ->map(fn (Banner $banner) => [
                'value' => (string) $banner->key,
                'label' => (string) $banner->name,
            ])
            ->values()
            ->all();
    }
}
