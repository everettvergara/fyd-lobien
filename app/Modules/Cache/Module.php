<?php

namespace App\Modules\Cache;

use App\Modules\Cache\Models\CacheSettings;
use App\Modules\Cache\Policies\CacheSettingsPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Cache';
    }

    public function policies(): array
    {
        return [
            CacheSettings::class => CacheSettingsPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('cache', 'view', 'View Cache Settings'),
            $this->permissionEntry('cache', 'edit', 'Edit Cache Settings'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem(
                'Cache',
                'admin.cache.index',
                'cache.view',
                'bi-lightning',
                'Administration',
                routePattern: 'admin.cache.*',
                sort: 95,
            ),
        ];
    }
}
