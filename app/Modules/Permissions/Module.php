<?php

namespace App\Modules\Permissions;

use App\Models\Permission;
use App\Modules\Permissions\Policies\PermissionPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Permissions';
    }

    public function policies(): array
    {
        return [
            Permission::class => PermissionPolicy::class,
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Permissions', 'admin.permissions.index', 'permissions.view', 'bi-key', 'Administration', sort: 90),
        ];
    }
}
