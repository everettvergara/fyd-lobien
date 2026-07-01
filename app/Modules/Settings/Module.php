<?php

namespace App\Modules\Settings;

use App\Models\Setting;
use App\Modules\Settings\Policies\SettingsPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Settings';
    }

    public function policies(): array
    {
        return [
            Setting::class => SettingsPolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Settings', 'admin.settings.index', 'settings.view', 'bi-gear', 'Administration', sort: 100),
        ];
    }
}
