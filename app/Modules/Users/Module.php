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

    public function menuItems(): array
    {
        return [
            $this->menuItem('Users', 'admin.users.index', 'users.view', 'bi-people', 'Administration', sort: 70),
        ];
    }
}
