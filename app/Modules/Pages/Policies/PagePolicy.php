<?php

namespace App\Modules\Pages\Policies;

use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Support\OwnContentAccess;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->hasPermission('pages.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        return OwnContentAccess::canManage($user, $page, 'pages.edit');
    }

    public function delete(User $user, Page $page): bool
    {
        if (! $user->hasPermission('pages.delete')) {
            return false;
        }

        return OwnContentAccess::canManage($user, $page, 'pages.edit');
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->hasPermission('pages.publish');
    }
}
