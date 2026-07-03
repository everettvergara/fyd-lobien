<?php

namespace App\Modules\PageManager\Policies;

use App\Models\User;
use App\Modules\PageManager\Models\Page;

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
        return $user->hasPermission('pages.edit');
    }

    public function delete(User $user, Page $page): bool
    {
        if ($page->is_system) {
            return false;
        }

        return $user->hasPermission('pages.delete');
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->hasPermission('pages.publish');
    }
}
