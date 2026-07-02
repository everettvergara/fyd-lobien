<?php

namespace App\Modules\Dashboard;

use App\Framework\Dashboard;
use App\Modules\Dashboard\Policies\DashboardPolicy;

class Module extends \App\Framework\Module
{
    public function name(): string
    {
        return 'Dashboard';
    }

    public function policies(): array
    {
        return [
            Dashboard::class => DashboardPolicy::class,
        ];
    }

    public function permissions(): array
    {
        return [
            $this->permissionEntry('dashboard', 'view', 'View Dashboard'),
        ];
    }

    public function menuItems(): array
    {
        return [
            $this->menuItem('Dashboard', 'admin.dashboard', 'dashboard.view', 'bi-speedometer2', sort: 10),
        ];
    }
}
