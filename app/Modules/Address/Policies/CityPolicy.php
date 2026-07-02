<?php

namespace App\Modules\Address\Policies;

use App\Models\User;
use App\Modules\Address\Models\City;

class CityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cities.view');
    }

    public function view(User $user, City $city): bool
    {
        return $user->hasPermission('cities.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('cities.create');
    }

    public function update(User $user, City $city): bool
    {
        return $user->hasPermission('cities.edit');
    }

    public function delete(User $user, City $city): bool
    {
        return $user->hasPermission('cities.delete');
    }
}
