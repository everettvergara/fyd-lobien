<?php

namespace App\Modules\Content;

use App\Framework\PublicBlock;
use App\Modules\Content\Blocks\ContentExtrasBlockResolver;
use App\Modules\Content\Blocks\ContentTypeListingBlockResolver;
use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Modules\Content\Policies\ContentPolicy;
use App\Modules\Content\Policies\ContentTypePolicy;
use App\Modules\Content\Services\ContentTypeListingService;
use App\Modules\Content\Support\ContentTypeKeyOptionsProvider;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Content';
    }

    public function policies(): array
    {
        return [
            Content::class => ContentPolicy::class,
            ContentType::class => ContentTypePolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('content', 'view', 'View Content'),
            $this->permissionEntry('content', 'create', 'Create Content'),
            $this->permissionEntry('content', 'edit', 'Edit Content'),
            $this->permissionEntry('content', 'delete', 'Delete Content'),
            $this->permissionEntry('content', 'publish', 'Publish Content'),
            $this->permissionEntry('content_types', 'view', 'View Content Types'),
            $this->permissionEntry('content_types', 'create', 'Create Content Types'),
            $this->permissionEntry('content_types', 'edit', 'Edit Content Types'),
            $this->permissionEntry('content_types', 'delete', 'Delete Content Types'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem(
                'Content Management',
                'admin.content.index',
                'content.view',
                'bi-collection',
                'Content',
                routePattern: 'admin.content.*',
                sort: 20,
            ),
            $this->menuItem(
                'Content Types',
                'admin.content-types.index',
                'content_types.view',
                'bi-tags',
                'Content',
                routePattern: 'admin.content-types.*',
                sort: 21,
            ),
        ];
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('content-extras')
                ->label('Content Extras')
                ->icon('bi-images')
                ->module($this->name())
                ->resolver(ContentExtrasBlockResolver::class)
                ->component('ContentExtrasBlock'),
            PublicBlock::make('content-type-listing')
                ->label('Content Type Listing')
                ->icon('bi-collection')
                ->module($this->name())
                ->resolver(ContentTypeListingBlockResolver::class)
                ->component('ContentTypeListingBlock')
                ->configSchema([
                    [
                        'key' => 'content_type_key',
                        'label' => 'Content Type',
                        'type' => 'select',
                        'required' => true,
                        'optionsProvider' => ContentTypeKeyOptionsProvider::class,
                    ],
                    [
                        'key' => 'per_page',
                        'label' => 'Items per page',
                        'type' => 'number',
                        'default' => ContentTypeListingService::PER_PAGE,
                        'min' => 1,
                        'max' => 100,
                        'help' => 'Maximum number of published entries to retrieve per page.',
                    ],
                ]),
        ];
    }
}
