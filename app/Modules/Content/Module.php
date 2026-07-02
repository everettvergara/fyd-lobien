<?php

namespace App\Modules\Content;

use App\Modules\Content\Models\Content;
use App\Modules\Content\Models\ContentType;
use App\Modules\Content\Policies\ContentPolicy;
use App\Modules\Content\Policies\ContentTypePolicy;

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
}
