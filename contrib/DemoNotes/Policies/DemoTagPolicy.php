<?php

namespace App\Modules\DemoNotes\Policies;

use App\Models\User;
use App\Modules\DemoNotes\Models\DemoTag;

class DemoTagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('demo_tags.view');
    }

    public function view(User $user, DemoTag $demoTag): bool
    {
        return $user->hasPermission('demo_tags.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('demo_tags.create');
    }

    public function update(User $user, DemoTag $demoTag): bool
    {
        return $user->hasPermission('demo_tags.edit');
    }

    public function delete(User $user, DemoTag $demoTag): bool
    {
        return $user->hasPermission('demo_tags.delete');
    }
}
