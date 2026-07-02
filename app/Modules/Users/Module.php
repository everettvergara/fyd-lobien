<?php

namespace App\Modules\Users;

use App\Models\User;
use App\Modules\Users\Policies\UserPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Users';
    }

    public function policies(): array
    {
        return [
            User::class => UserPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('users', 'view', 'View Users'),
            $this->permissionEntry('users', 'create', 'Create Users'),
            $this->permissionEntry('users', 'edit', 'Edit Users'),
            $this->permissionEntry('users', 'delete', 'Delete Users'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Users', 'admin.users.index', 'users.view', 'bi-people', 'Administration', sort: 70),
        ];
    }
}
