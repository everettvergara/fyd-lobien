<?php

namespace App\Modules\Dashboard\Policies;

use App\Framework\Dashboard;
use App\Models\User;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('dashboard.view');
    }

    public function view(User $user, Dashboard $dashboard): bool
    {
        return $user->hasPermission('dashboard.view');
    }
}
