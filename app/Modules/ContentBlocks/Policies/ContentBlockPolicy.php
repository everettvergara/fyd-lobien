<?php

namespace App\Modules\ContentBlocks\Policies;

use App\Models\User;
use App\Modules\ContentBlocks\Models\ContentBlock;

class ContentBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('content_blocks.view');
    }

    public function view(User $user, ContentBlock $contentBlock): bool
    {
        return $user->hasPermission('content_blocks.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('content_blocks.create');
    }

    public function update(User $user, ContentBlock $contentBlock): bool
    {
        return $user->hasPermission('content_blocks.edit');
    }

    public function delete(User $user, ContentBlock $contentBlock): bool
    {
        return $user->hasPermission('content_blocks.delete');
    }

    public function publish(User $user, ContentBlock $contentBlock): bool
    {
        return $user->hasPermission('content_blocks.publish');
    }

    public function archive(User $user, ContentBlock $contentBlock): bool
    {
        return $user->hasPermission('content_blocks.archive');
    }
}
