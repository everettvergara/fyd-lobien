<?php

namespace App\Modules\Content\Policies;

use App\Models\User;
use App\Modules\Content\Models\Content;
use App\Support\OwnContentAccess;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content.view');
    }

    public function view(User $user, Content $content): bool
    {
        return $user->hasPermission('content.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content.create');
    }

    public function update(User $user, Content $content): bool
    {
        return OwnContentAccess::canManage($user, $content, 'content.edit');
    }

    public function delete(User $user, Content $content): bool
    {
        if (! $user->hasPermission('content.delete')) {
            return false;
        }

        return OwnContentAccess::canManage($user, $content, 'content.edit');
    }

    public function publish(User $user, Content $content): bool
    {
        return $user->hasPermission('content.publish');
    }
}
