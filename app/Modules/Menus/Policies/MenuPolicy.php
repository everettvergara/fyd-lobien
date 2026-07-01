<?php

namespace App\Modules\Menus\Policies;

use App\Models\User;
use App\Modules\Menus\Models\Menu;

class MenuPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('menus.view'); }
    public function view(User $user, Menu $menu): bool { return $user->hasPermission('menus.view'); }
    public function create(User $user): bool { return $user->hasPermission('menus.create'); }
    public function update(User $user, Menu $menu): bool { return $user->hasPermission('menus.edit'); }
    public function delete(User $user, Menu $menu): bool { return $user->hasPermission('menus.delete'); }
}
