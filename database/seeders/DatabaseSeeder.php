<?php

namespace Database\Seeders;

use App\Modules\Authentication\Seeders\AuthenticationSeeder;
use App\Modules\Permissions\Seeders\PermissionsSeeder;
use App\Modules\Roles\Seeders\RolesSeeder;
use App\Modules\Settings\Seeders\SettingsSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            RolesSeeder::class,
            SettingsSeeder::class,
            AuthenticationSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
