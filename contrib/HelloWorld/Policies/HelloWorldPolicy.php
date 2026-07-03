<?php

namespace App\Modules\HelloWorld\Policies;

use App\Models\User;
use App\Modules\HelloWorld\HelloWorldAccess;

class HelloWorldPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hello_world.view');
    }
}
