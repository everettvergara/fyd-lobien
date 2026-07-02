<?php

namespace App\Modules\Cache\Policies;

use App\Models\User;
use App\Modules\Cache\Models\CacheSettings;

class CacheSettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cache.view');
    }

    public function update(User $user, ?CacheSettings $settings = null): bool
    {
        return $user->hasPermission('cache.edit');
    }
}
