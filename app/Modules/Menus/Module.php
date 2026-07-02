<?php

namespace App\Modules\Menus;

use App\Modules\Menus\Models\Menu;
use App\Modules\Menus\Policies\MenuPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Menus';
    }

    public function policies(): array
    {
        return [
            Menu::class => MenuPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('menus', 'view', 'View Menus'),
            $this->permissionEntry('menus', 'create', 'Create Menus'),
            $this->permissionEntry('menus', 'edit', 'Edit Menus'),
            $this->permissionEntry('menus', 'delete', 'Delete Menus'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Menus', 'admin.menus.index', 'menus.view', 'bi-list-nested', 'Content', sort: 50),
        ];
    }
}
