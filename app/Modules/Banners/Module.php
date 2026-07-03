<?php

namespace App\Modules\Banners;

use App\Framework\PublicBlock;
use App\Modules\Banners\Blocks\BannerBlockResolver;
use App\Modules\Banners\Models\Banner;
use App\Modules\Banners\Policies\BannerPolicy;
use App\Modules\Banners\Support\BannerKeyOptionsProvider;

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

    public function permissions(): array
    {
        return [
            $this->permissionEntry('banners', 'view', 'View Banners'),
            $this->permissionEntry('banners', 'create', 'Create Banners'),
            $this->permissionEntry('banners', 'edit', 'Edit Banners'),
            $this->permissionEntry('banners', 'delete', 'Delete Banners'),
            $this->permissionEntry('banners', 'publish', 'Publish Banners'),
            $this->permissionEntry('banners', 'archive', 'Archive Banners'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Banners', 'admin.banners.index', 'banners.view', 'bi-image', 'Content', sort: 40),
        ];
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('banner')
                ->label('Banner')
                ->icon('bi-image')
                ->module($this->name())
                ->resolver(BannerBlockResolver::class)
                ->component('BannerBlock')
                ->configSchema([
                    [
                        'key' => 'banner_key',
                        'label' => 'Banner',
                        'type' => 'select',
                        'required' => true,
                        'optionsProvider' => BannerKeyOptionsProvider::class,
                    ],
                ]),
        ];
    }
}
