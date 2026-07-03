<?php

namespace App\Modules\PageManager\Policies;

use App\Models\User;
use App\Modules\PageManager\Models\PageMaster;

class PageMasterPolicy
{
    public function update(User $user, PageMaster $pageMaster): bool
    {
        return $user->hasPermission('page-master.edit');
    }
}
