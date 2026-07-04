<?php

namespace App\Modules\PropertyListings\Policies;

use App\Models\User;
use App\Modules\PropertyListings\Models\ListingLookup;

class ListingLookupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('listings.lookups.view');
    }

    public function view(User $user, ListingLookup $lookup): bool
    {
        return $user->hasPermission('listings.lookups.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('listings.lookups.create');
    }

    public function update(User $user, ListingLookup $lookup): bool
    {
        return $user->hasPermission('listings.lookups.edit');
    }

    public function delete(User $user, ListingLookup $lookup): bool
    {
        return $user->hasPermission('listings.lookups.delete');
    }
}
