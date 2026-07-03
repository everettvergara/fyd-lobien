<?php

namespace App\Modules\ModuleManager;

use App\Framework\ModuleManager;
use App\Modules\ModuleManager\Policies\ModuleManagerPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'ModuleManager';
    }

    public function policies(): array
    {
        return [
            ModuleManager::class => ModuleManagerPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('modules', 'view', 'View Modules'),
            $this->permissionEntry('modules', 'install', 'Install Modules'),
            $this->permissionEntry('modules', 'disable', 'Disable Modules'),
            $this->permissionEntry('modules', 'enable', 'Enable Modules'),
            $this->permissionEntry('modules', 'uninstall', 'Uninstall Modules'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Modules', 'admin.modules.index', 'modules.view', 'bi-puzzle', 'Administration', sort: 96),
        ];
    }
}
