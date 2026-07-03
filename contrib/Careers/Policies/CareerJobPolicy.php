<?php

namespace App\Modules\Careers\Policies;

use App\Models\User;
use App\Modules\Careers\Models\CareerJob;

class CareerJobPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('careers.jobs.view');
    }

    public function view(User $user, CareerJob $job): bool
    {
        return $user->hasPermission('careers.jobs.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('careers.jobs.create');
    }

    public function update(User $user, CareerJob $job): bool
    {
        return $user->hasPermission('careers.jobs.edit');
    }

    public function delete(User $user, CareerJob $job): bool
    {
        return $user->hasPermission('careers.jobs.delete');
    }
}
