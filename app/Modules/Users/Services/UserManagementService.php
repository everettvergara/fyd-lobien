<?php

namespace App\Modules\Users\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use App\Notifications\AccountDeactivatedNotification;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserManagementService
{
    public function create(array $data, ?array $roleIds = null): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
            'email_verified_at' => $data['status'] === UserStatus::Active ? now() : null,
        ]);

        if (! empty($roleIds)) {
            $user->syncRoles($roleIds);
        }

        ActivityLogger::log('users', 'created', $user, ['name' => $user->name]);

        return $user;
    }

    public function update(User $user, array $data, ?array $roleIds = null, ?string $password = null): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
        ]);

        if ($password) {
            $user->password = Hash::make($password);
        }

        $user->save();
        $user->syncRoles($roleIds ?? []);

        ActivityLogger::log('users', 'updated', $user, ['name' => $user->name]);

        return $user;
    }

    public function delete(User $user): void
    {
        ActivityLogger::log('users', 'deleted', $user, ['name' => $user->name]);
        $user->delete();
    }

    public function activate(User $user): void
    {
        $user->update(['status' => UserStatus::Active]);
        $user->notify(new AccountActivatedNotification);
        ActivityLogger::log('users', 'activated', $user);
    }

    public function deactivate(User $user): void
    {
        $user->update(['status' => UserStatus::Inactive]);
        $user->notify(new AccountDeactivatedNotification);
        ActivityLogger::log('users', 'deactivated', $user);
    }

    public function suspend(User $user): void
    {
        $user->update(['status' => UserStatus::Suspended]);
        ActivityLogger::log('users', 'suspended', $user);
    }

    public function sendPasswordReset(User $user): void
    {
        Password::sendResetLink(['email' => $user->email]);
        ActivityLogger::log('users', 'updated', $user, ['action' => 'password_reset_sent']);
    }
}
