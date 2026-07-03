<?php

namespace App\Modules\WebForms\Policies;

use App\Models\User;
use App\Modules\WebForms\Models\Webform;

class WebformPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('webforms.view');
    }

    public function view(User $user, Webform $webform): bool
    {
        return $user->hasPermission('webforms.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('webforms.create');
    }

    public function update(User $user, Webform $webform): bool
    {
        return $user->hasPermission('webforms.edit');
    }

    public function delete(User $user, Webform $webform): bool
    {
        return $user->hasPermission('webforms.delete');
    }
}
