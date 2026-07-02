<?php

namespace App\Modules\Media;

use App\Models\Media;
use App\Modules\Media\Policies\MediaPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Media';
    }

    public function policies(): array
    {
        return [
            Media::class => MediaPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('media', 'view', 'View Media'),
            $this->permissionEntry('media', 'create', 'Create Media'),
            $this->permissionEntry('media', 'edit', 'Edit Media'),
            $this->permissionEntry('media', 'delete', 'Delete Media'),
            $this->permissionEntry('media', 'download', 'Download Media'),
            $this->permissionEntry('media', 'replace', 'Replace Media'),
            $this->permissionEntry('media', 'bulk_delete', 'Bulk Delete Media'),
            $this->permissionEntry('media', 'bulk_download', 'Bulk Download Media'),
            $this->permissionEntry('media', 'folders', 'Manage Media Folders'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Media', 'admin.media.index', 'media.view', 'bi-folder2-open', 'Content', sort: 60),
        ];
    }
}
