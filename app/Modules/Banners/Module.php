<?php

namespace App\Modules\Banners;

use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Policies\BannerPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Banners';
    }

    public function policies(): array
    {
        return [
            Banner::class => BannerPolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Banners', 'admin.banners.index', 'banners.view', 'bi-image', 'Content', sort: 40),
        ];
    }
}
