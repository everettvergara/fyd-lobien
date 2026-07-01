<?php

namespace App\Modules\Banners\Policies;

use App\Models\User;
use App\Modules\Banners\Models\Banner;

class BannerPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('banners.view'); }
    public function view(User $user, Banner $banner): bool { return $user->hasPermission('banners.view'); }
    public function create(User $user): bool { return $user->hasPermission('banners.create'); }
    public function update(User $user, Banner $banner): bool { return $user->hasPermission('banners.edit'); }
    public function delete(User $user, Banner $banner): bool { return $user->hasPermission('banners.delete'); }
    public function publish(User $user, Banner $banner): bool { return $user->hasPermission('banners.publish'); }
}
