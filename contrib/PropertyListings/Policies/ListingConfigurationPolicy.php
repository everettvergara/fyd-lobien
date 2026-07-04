<?php

namespace App\Modules\PropertyListings\Policies;

use App\Models\User;
use App\Modules\PropertyListings\Models\ListingConfiguration;

class ListingConfigurationPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasPermission('listings.configuration.manage');
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }
}
