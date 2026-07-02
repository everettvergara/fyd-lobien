<?php

namespace App\Modules\Banners\Services;

use App\Modules\Banners\Models\BannerTemplate;
use Illuminate\Support\Collection;

class BannerTemplateService
{
    public function active(): Collection
    {
        return BannerTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function options(): array
    {
        return $this->active()->pluck('name', 'id')->all();
    }
}
