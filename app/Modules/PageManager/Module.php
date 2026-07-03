<?php

namespace App\Modules\PageManager;

use App\Framework\PublicBlock;
use App\Modules\PageManager\Blocks\FeaturedContentBlockResolver;
use App\Modules\PageManager\Blocks\LatestArticlesBlockResolver;
use App\Modules\PageManager\Blocks\PageBodyBlockResolver;
use App\Modules\PageManager\Blocks\PageHeaderBlockResolver;
use App\Modules\PageManager\Database\Seeders\PageManagerSeeder;
use App\Modules\PageManager\Models\Page;
use App\Modules\PageManager\Models\PageMaster;
use App\Modules\PageManager\Policies\PageMasterPolicy;
use App\Modules\PageManager\Policies\PagePolicy;
use App\Modules\PageManager\Support\ContentTypeOptionsProvider;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'PageManager';
    }

    public function policies(): array
    {
        return [
            Page::class => PagePolicy::class,
            PageMaster::class => PageMasterPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('pages', 'view', 'View Pages'),
            $this->permissionEntry('pages', 'create', 'Create Pages'),
            $this->permissionEntry('pages', 'edit', 'Edit Pages'),
            $this->permissionEntry('pages', 'delete', 'Delete Pages'),
            $this->permissionEntry('pages', 'publish', 'Publish Pages'),
            $this->permissionEntry('page-master', 'edit', 'Edit Page Master'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Pages', 'admin.pages.index', 'pages.view', 'bi-layout-text-window-reverse', 'Content', 'admin.pages.*', sort: 15),
            $this->menuItem('Page Master', 'admin.page-master.edit', 'page-master.edit', 'bi-layers', 'Content', 'admin.page-master.*', sort: 16),
        ];
    }

    public function seeders(): array
    {
        return [PageManagerSeeder::class];
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('page-header')
                ->label('Page Header')
                ->icon('bi-type-h1')
                ->module($this->name())
                ->resolver(PageHeaderBlockResolver::class)
                ->component('PageHeaderBlock'),
            PublicBlock::make('page-body')
                ->label('Page Body')
                ->icon('bi-file-richtext')
                ->module($this->name())
                ->resolver(PageBodyBlockResolver::class)
                ->component('PageBodyBlock'),
            PublicBlock::make('featured-content')
                ->label('Featured Content')
                ->icon('bi-grid')
                ->module($this->name())
                ->resolver(FeaturedContentBlockResolver::class)
                ->component('FeaturedContentBlock')
                ->configSchema([
                    [
                        'key' => 'heading',
                        'label' => 'Heading',
                        'type' => 'text',
                        'default' => 'Featured Content',
                    ],
                    [
                        'key' => 'limit',
                        'label' => 'Limit',
                        'type' => 'number',
                        'default' => 3,
                        'min' => 1,
                        'max' => 12,
                    ],
                    [
                        'key' => 'content_type',
                        'label' => 'Content Type',
                        'type' => 'select',
                        'default' => 'page',
                        'optionsProvider' => ContentTypeOptionsProvider::class,
                    ],
                ]),
            PublicBlock::make('latest-articles')
                ->label('Latest Articles')
                ->icon('bi-newspaper')
                ->module($this->name())
                ->resolver(LatestArticlesBlockResolver::class)
                ->component('LatestArticlesBlock')
                ->configSchema([
                    [
                        'key' => 'heading',
                        'label' => 'Heading',
                        'type' => 'text',
                        'default' => 'Latest Articles',
                    ],
                    [
                        'key' => 'limit',
                        'label' => 'Limit',
                        'type' => 'number',
                        'default' => 3,
                        'min' => 1,
                        'max' => 12,
                    ],
                ]),
        ];
    }
}
