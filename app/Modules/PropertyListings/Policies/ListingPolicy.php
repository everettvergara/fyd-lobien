<?php

namespace App\Modules\PropertyListings\Policies;

use App\Models\User;
use App\Modules\PropertyListings\Models\Listing;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('listings.view');
    }

    public function view(User $user, Listing $listing): bool
    {
        return $user->hasPermission('listings.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('listings.create');
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->hasPermission('listings.edit');
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->hasPermission('listings.delete');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('listings.export');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('listings.import');
    }

    public function batchAssets(User $user): bool
    {
        return $user->hasPermission('listings.assets.batch');
    }
}
