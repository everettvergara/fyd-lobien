<?php

namespace App\Modules\Address\Policies;

use App\Models\User;
use App\Modules\Address\Models\Province;

class ProvincePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('provinces.view');
    }

    public function view(User $user, Province $province): bool
    {
        return $user->hasPermission('provinces.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('provinces.create');
    }

    public function update(User $user, Province $province): bool
    {
        return $user->hasPermission('provinces.edit');
    }

    public function delete(User $user, Province $province): bool
    {
        return $user->hasPermission('provinces.delete');
    }
}
