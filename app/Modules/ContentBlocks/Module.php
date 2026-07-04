<?php

namespace App\Modules\ContentBlocks;

use App\Framework\PublicBlock;
use App\Modules\ContentBlocks\Blocks\ContentBlockBlockResolver;
use App\Modules\ContentBlocks\Database\Seeders\ContentBlockSeeder as ContentBlockSeederClass;
use App\Modules\ContentBlocks\Models\ContentBlock;
use App\Modules\ContentBlocks\Policies\ContentBlockPolicy;
use App\Modules\ContentBlocks\Support\ContentBlockKeyOptionsProvider;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'ContentBlocks';
    }

    public function policies(): array
    {
        return [
            ContentBlock::class => ContentBlockPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('content_blocks', 'view', 'View Content Blocks'),
            $this->permissionEntry('content_blocks', 'create', 'Create Content Blocks'),
            $this->permissionEntry('content_blocks', 'edit', 'Edit Content Blocks'),
            $this->permissionEntry('content_blocks', 'delete', 'Delete Content Blocks'),
            $this->permissionEntry('content_blocks', 'publish', 'Publish Content Blocks'),
            $this->permissionEntry('content_blocks', 'archive', 'Archive Content Blocks'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem(
                'Content Blocks',
                'admin.content-blocks.index',
                'content_blocks.view',
                ContentBlockSeederClass::MENU_ICON,
                'Content',
                routePattern: 'admin.content-blocks.*',
                sort: 35,
            ),
        ];
    }

    public function seeders(): array
    {
        return [ContentBlockSeederClass::class];
    }

    public function publicBlocks(): array
    {
        return [
            PublicBlock::make('content-block')
                ->label('Content Block')
                ->icon(ContentBlockSeederClass::MENU_ICON)
                ->module($this->name())
                ->resolver(ContentBlockBlockResolver::class)
                ->component('ContentBlockBlock')
                ->configSchema([
                    [
                        'key' => 'content_block_key',
                        'label' => 'Content Block',
                        'type' => 'select',
                        'required' => true,
                        'optionsProvider' => ContentBlockKeyOptionsProvider::class,
                    ],
                ]),
        ];
    }
}
