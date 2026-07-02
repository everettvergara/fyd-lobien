<?php

namespace App\Modules\Media\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('media.view'); }
    public function view(User $user, Media $media): bool { return $user->hasPermission('media.view'); }
    public function create(User $user): bool { return $user->hasPermission('media.create'); }
    public function update(User $user, Media $media): bool { return $user->hasPermission('media.edit'); }
    public function delete(User $user, Media $media): bool { return $user->hasPermission('media.delete'); }
    public function download(User $user, Media $media): bool { return $user->hasPermission('media.download'); }
    public function replace(User $user, Media $media): bool { return $user->hasPermission('media.replace'); }
    public function bulkDelete(User $user): bool { return $user->hasPermission('media.bulk_delete'); }
    public function bulkDownload(User $user): bool { return $user->hasPermission('media.bulk_download'); }
    public function manageFolders(User $user): bool { return $user->hasPermission('media.folders'); }
}
