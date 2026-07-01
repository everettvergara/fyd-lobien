<?php

namespace App\Modules\Sessions\Policies;

use App\Models\DatabaseSession;
use App\Models\User;

class SessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sessions.view');
    }

    public function delete(User $user, DatabaseSession $session): bool
    {
        return $user->hasPermission('sessions.delete');
    }
}
