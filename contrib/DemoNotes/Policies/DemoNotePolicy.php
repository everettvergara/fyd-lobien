<?php

namespace App\Modules\DemoNotes\Policies;

use App\Models\User;
use App\Modules\DemoNotes\Models\DemoNote;

class DemoNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('demo_notes.view');
    }

    public function view(User $user, DemoNote $demoNote): bool
    {
        return $user->hasPermission('demo_notes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('demo_notes.create');
    }

    public function update(User $user, DemoNote $demoNote): bool
    {
        return $user->hasPermission('demo_notes.edit');
    }

    public function delete(User $user, DemoNote $demoNote): bool
    {
        return $user->hasPermission('demo_notes.delete');
    }
}
