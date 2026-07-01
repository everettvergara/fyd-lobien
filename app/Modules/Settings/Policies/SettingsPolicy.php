<?php

namespace App\Modules\Settings\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('settings.view');
    }

    public function update(User $user, mixed $setting = null): bool
    {
        return $user->hasPermission('settings.edit');
    }
}
