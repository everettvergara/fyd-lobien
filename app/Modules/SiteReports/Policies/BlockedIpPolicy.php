<?php

namespace App\Modules\SiteReports\Policies;

use App\Models\User;
use App\Modules\SiteReports\Models\BlockedIp;

class BlockedIpPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('site_reports.block');
    }

    public function delete(User $user, BlockedIp $blockedIp): bool
    {
        return $user->hasPermission('site_reports.block');
    }
}
