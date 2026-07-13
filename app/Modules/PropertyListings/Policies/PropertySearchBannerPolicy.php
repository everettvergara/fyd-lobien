<?php

namespace App\Modules\PropertyListings\Policies;

use App\Models\User;
use App\Modules\PropertyListings\Models\PropertySearchBanner;

class PropertySearchBannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('listings.search_banners.view');
    }

    public function view(User $user, PropertySearchBanner $banner): bool
    {
        return $user->hasPermission('listings.search_banners.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('listings.search_banners.create');
    }

    public function update(User $user, PropertySearchBanner $banner): bool
    {
        return $user->hasPermission('listings.search_banners.edit');
    }

    public function delete(User $user, PropertySearchBanner $banner): bool
    {
        return $user->hasPermission('listings.search_banners.delete');
    }
}
