<?php

namespace App\Modules\ModuleManager\Policies;

use App\Framework\ModuleManager;
use App\Models\User;

class ModuleManagerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('modules.view');
    }

    public function install(User $user): bool
    {
        return $user->hasPermission('modules.install');
    }

    public function disable(User $user): bool
    {
        return $user->hasPermission('modules.disable');
    }

    public function enable(User $user): bool
    {
        return $user->hasPermission('modules.enable');
    }

    public function uninstall(User $user): bool
    {
        return $user->hasPermission('modules.uninstall');
    }
}
