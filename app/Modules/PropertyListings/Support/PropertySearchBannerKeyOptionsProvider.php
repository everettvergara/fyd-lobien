<?php

namespace App\Modules\PropertyListings\Support;

use App\Contracts\BlockConfigOptionsProvider;
use App\Modules\PropertyListings\Models\PropertySearchBanner;

class PropertySearchBannerKeyOptionsProvider implements BlockConfigOptionsProvider
{
    public function options(): array
    {
        return PropertySearchBanner::query()
            ->active()
            ->orderBy('name')
            ->get(['key', 'name'])
            ->map(fn (PropertySearchBanner $banner) => [
                'value' => (string) $banner->key,
                'label' => (string) $banner->name,
            ])
            ->values()
            ->all();
    }
}
