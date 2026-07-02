<?php

namespace App\Modules\SEO\Policies;

use App\Models\User;
use App\Modules\SEO\Models\SeoSettings;

class SeoSettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('seo.view');
    }

    public function update(User $user, ?SeoSettings $settings = null): bool
    {
        return $user->hasPermission('seo.edit');
    }
}
