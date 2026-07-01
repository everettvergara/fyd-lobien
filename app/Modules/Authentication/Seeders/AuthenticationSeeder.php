<?php

namespace App\Modules\Authentication\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@fyd.local'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        if ($role = \App\Models\Role::where('name', 'super_administrator')->first()) {
            $user->syncRoles([$role->id]);
        }
    }
}
