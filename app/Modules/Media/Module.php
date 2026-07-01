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

    public function menuItems(): array
    {
        return [
            $this->menuItem('Media', 'admin.media.index', 'media.view', 'bi-folder2-open', 'Content', sort: 60),
        ];
    }
}
