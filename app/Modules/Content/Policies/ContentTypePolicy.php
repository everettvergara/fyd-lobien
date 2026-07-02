<?php

namespace App\Modules\Content\Policies;

use App\Models\User;
use App\Modules\Content\Models\ContentType;

class ContentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content_types.view');
    }

    public function view(User $user, ContentType $contentType): bool
    {
        return $user->hasPermission('content_types.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content_types.create');
    }

    public function update(User $user, ContentType $contentType): bool
    {
        return $user->hasPermission('content_types.edit');
    }

    public function delete(User $user, ContentType $contentType): bool
    {
        return $user->hasPermission('content_types.delete');
    }
}
