<?php

namespace App\Modules\Posts\Policies;

use App\Models\User;
use App\Modules\Posts\Models\Post;
use App\Support\OwnContentAccess;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('posts.view');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('posts.create');
    }

    public function update(User $user, Post $post): bool
    {
        return OwnContentAccess::canManage($user, $post, 'posts.edit');
    }

    public function delete(User $user, Post $post): bool
    {
        if (! $user->hasPermission('posts.delete')) {
            return false;
        }

        return OwnContentAccess::canManage($user, $post, 'posts.edit');
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.publish');
    }
}
