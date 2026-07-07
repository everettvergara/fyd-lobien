<?php

namespace App\Modules\Careers\Policies;

use App\Models\User;
use App\Modules\Careers\Models\CareerApplication;

class CareerApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('careers.applications.view');
    }

    public function view(User $user, CareerApplication $application): bool
    {
        return $user->hasPermission('careers.applications.view');
    }

    public function delete(User $user, CareerApplication $application): bool
    {
        return $user->hasPermission('careers.applications.delete');
    }
}
