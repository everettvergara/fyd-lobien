<?php

namespace App\Modules\Themes\Policies;

use App\Models\User;
use App\Modules\Themes\Models\ThemeSettings;

class ThemeSettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('themes.view');
    }

    public function update(User $user, ?ThemeSettings $settings = null): bool
    {
        return $user->hasPermission('themes.activate');
    }

    public function install(User $user): bool
    {
        return $user->hasPermission('themes.install');
    }
}
