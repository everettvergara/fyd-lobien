<?php

namespace App\Modules\Themes;

use App\Modules\Themes\Models\ThemeSettings;
use App\Modules\Themes\Policies\ThemeSettingsPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Themes';
    }

    public function policies(): array
    {
        return [
            ThemeSettings::class => ThemeSettingsPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('themes', 'view', 'View Public Themes'),
            $this->permissionEntry('themes', 'activate', 'Activate Public Themes'),
            $this->permissionEntry('themes', 'install', 'Install Public Themes'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem(
                'Public Themes',
                'admin.themes.index',
                'themes.view',
                'bi-palette',
                'Administration',
                routePattern: 'admin.themes.*',
                sort: 97,
            ),
        ];
    }
}
