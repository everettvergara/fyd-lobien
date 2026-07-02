<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OwnContentAccess
{
    public static function managesOwnContentOnly(User $user): bool
    {
        return $user->hasRole('author')
            && ! $user->hasPermission('content.publish');
    }

    public static function canManage(User $user, Model $model, string $editPermission): bool
    {
        if (! $user->hasPermission($editPermission)) {
            return false;
        }

        if (! self::managesOwnContentOnly($user)) {
            return true;
        }

        return isset($model->author_id) && (int) $model->author_id === (int) $user->id;
    }
}
