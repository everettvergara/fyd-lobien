<?php

namespace App\Modules\SiteReports\Policies;

use App\Models\User;
use App\Modules\SiteReports\Models\SiteVisit;

class SiteVisitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('site_reports.view');
    }
}
