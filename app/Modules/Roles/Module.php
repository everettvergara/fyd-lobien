<?php

namespace App\Modules\Roles;

use App\Models\Role;
use App\Modules\Roles\Policies\RolePolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Roles';
    }

    public function policies(): array
    {
        return [
            Role::class => RolePolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('roles', 'view', 'View Roles'),
            $this->permissionEntry('roles', 'create', 'Create Roles'),
            $this->permissionEntry('roles', 'edit', 'Edit Roles'),
            $this->permissionEntry('roles', 'delete', 'Delete Roles'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Roles', 'admin.roles.index', 'roles.view', 'bi-shield-check', 'Administration', sort: 80),
        ];
    }
}
